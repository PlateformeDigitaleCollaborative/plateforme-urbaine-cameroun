<?php

namespace App\DataFixtures\Factory;

use App\Entity\File\FileObject;
use App\Entity\File\MediaObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;

/**
 * Fabrique de fichiers pour les fixtures.
 *
 * VichUploader *déplace* le fichier qu'on lui confie : on ne lui passe donc
 * jamais directement un fichier versionné du dépôt, mais toujours une copie
 * temporaire. Les fichiers uploadés atterrissent ensuite dans
 * public/media (MediaObject) ou public/files (FileObject).
 */
class FixtureFileFactory
{
    /**
     * Répertoire (relatif à symfony/) contenant les images de fixtures.
     */
    public const IMAGES_DIR = 'fixtures/images';

    public function __construct(private readonly string $projectDir)
    {
    }

    /**
     * Crée un MediaObject (image) à partir d'un fichier du dépôt.
     *
     * @param string $relativePath chemin relatif à symfony/, ex :
     *                             "fixtures/images/divercity/divercity-espace.jpg"
     *
     * @return MediaObject|null null si le fichier source est absent (on ne
     *                          casse pas le chargement des fixtures pour autant)
     */
    public function createMediaObject(string $relativePath, ?string $originalName = null): ?MediaObject
    {
        $uploadedFile = $this->prepareUploadedFile($relativePath, $originalName);

        if (null === $uploadedFile) {
            return null;
        }

        $mediaObject = new MediaObject();
        $mediaObject->file = $uploadedFile;

        return $mediaObject;
    }

    /**
     * Crée un FileObject (document) à partir d'un fichier du dépôt.
     *
     * @param string $relativePath chemin relatif à symfony/
     */
    public function createFileObject(string $relativePath, ?string $originalName = null): ?FileObject
    {
        $uploadedFile = $this->prepareUploadedFile($relativePath, $originalName);

        if (null === $uploadedFile) {
            return null;
        }

        $fileObject = new FileObject();
        $fileObject->file = $uploadedFile;

        return $fileObject;
    }

    /**
     * Crée un FileObject PDF généré à la volée (ordre du jour, document
     * ressource, rapport d'activité...). Évite de versionner des PDF de
     * démonstration dans le dépôt.
     *
     * @param string[] $lines lignes de texte imprimées dans le PDF
     */
    public function createGeneratedPdf(string $originalName, array $lines): FileObject
    {
        $temporaryPath = $this->temporaryPath($originalName);
        file_put_contents($temporaryPath, $this->buildPdf($lines));

        $fileObject = new FileObject();
        $fileObject->file = new UploadedFile(
            $temporaryPath,
            $originalName,
            'application/pdf',
            null,
            true
        );

        return $fileObject;
    }

    private function prepareUploadedFile(string $relativePath, ?string $originalName): ?UploadedFile
    {
        $absolutePath = $this->projectDir.'/'.ltrim($relativePath, '/');

        if (!is_file($absolutePath)) {
            return null;
        }

        $originalName ??= basename($absolutePath);
        $temporaryPath = $this->temporaryPath($originalName);

        if (!@copy($absolutePath, $temporaryPath)) {
            return null;
        }

        return new UploadedFile(
            $temporaryPath,
            $originalName,
            MimeTypes::getDefault()->guessMimeType($temporaryPath),
            null,
            true
        );
    }

    private function temporaryPath(string $originalName): string
    {
        return sys_get_temp_dir().'/'.uniqid('fixture_', true).'_'.basename($originalName);
    }

    /**
     * Génère un PDF minimaliste mais valide (une page A4, police Helvetica),
     * sans dépendance externe.
     *
     * @param string[] $lines
     */
    private function buildPdf(array $lines): string
    {
        $stream = "BT\n/F1 14 Tf\n72 780 Td\n";

        foreach ($lines as $line) {
            $stream .= '('.$this->escapePdfText($line).") Tj\n0 -22 Td\n";
        }

        $stream .= 'ET';

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            4 => '<< /Length '.strlen($stream)." >>\nstream\n".$stream."\nendstream",
            5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $number => $body) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number." 0 obj\n".$body."\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $size = count($objects) + 1;

        $pdf .= "xref\n0 ".$size."\n";
        $pdf .= "0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size ".$size." /Root 1 0 R >>\nstartxref\n".$xrefOffset."\n%%EOF";

        return $pdf;
    }

    private function escapePdfText(string $text): string
    {
        // Les chaînes PDF sont encodées en WinAnsi (cf. /Encoding ci-dessus).
        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $text);

        return str_replace(
            ['\\', '(', ')', "\r", "\n"],
            ['\\\\', '\\(', '\\)', '', ''],
            false === $converted ? $text : $converted
        );
    }
}
