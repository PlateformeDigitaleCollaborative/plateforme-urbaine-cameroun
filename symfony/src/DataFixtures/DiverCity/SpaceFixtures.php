<?php

namespace App\DataFixtures\DiverCity;

use App\DataFixtures\Factory\FixtureFileFactory;
use App\Entity\DiverCity\Space;
use App\Entity\GeoData;
use App\Entity\User\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixture de l'espace DiverCity ("tiers-lieu"). Une seule ligne pour le
 * moment : l'application ne gère qu'un espace principal (mainSpace).
 *
 * Charge également les photos du carrousel affiché sur la fiche de l'espace,
 * depuis fixtures/images/divercity/.
 *
 * Prérequis : suppose que les utilisateurs de fixtures/users.yaml
 * (admin@test.com, editor@test.com, ...) sont déjà en base — `make run-fixtures`
 * lance `hautelook:fixtures:load` avant `doctrine:fixtures:load`, sinon
 * createdBy restera simplement à null.
 */
class SpaceFixtures extends Fixture
{
    public const REFERENCE_MAIN_SPACE = 'divercity_space_main';

    /**
     * Photos du carrousel, dans l'ordre d'affichage.
     * Les fichiers sont attendus dans symfony/fixtures/images/divercity/.
     */
    public const PHOTOS = [
        'divercity-ceremonie.jpg',
        'divercity-panel.jpg',
        'divercity-equipe.jpg',
        'divercity-espace.jpg',
    ];

    public function __construct(private readonly FixtureFileFactory $fileFactory)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $geoData = new GeoData();
        $geoData->setName('Golf Bastos, Yaoundé');
        $geoData->setOsmId('123456789');
        $geoData->setLatitude(3.9007199);
        $geoData->setLongitude(11.5132397);

        $space = new Space();
        $space->setName('DiverCity Space');
        $space->setDescription(
            '<p style="text-align: justify;">Le <b>DiverCity Space</b> est un tiers-lieu institutionnel d\'innovation urbaine, conçu dans le cadre du projet PUC, avec l\'appui d\'Expertise France et de l\'Union européenne. Placé sous la tutelle du Ministère de l\'Habitat et du Développement Urbain (MINDHU), il est porté par la Plateforme Nationale des Acteurs de l\'Urbain (PNAU), un réseau regroupant 68 institutions publiques, universitaires, professionnelles et associatives. Cette initiative incarne la volonté du Gouvernement de rapprocher les acteurs institutionnels, techniques et citoyens, et d\'en faire un espace fédérateur pour l\'innovation urbaine camerounaise.</p>'
        );
        $space->setMaxCapacity(50);
        $space->setContact('+237 6 90 00 00 00');
        $space->setEmail('divercity@bureaudexpertise.cm');
        $space->setVideoLink('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
        $space->setEquipment('Vidéoprojecteur;Écran de projection;Wifi haut débit;Système de sonorisation;Tableau blanc;Chaises et tables modulables;Machine à café');
        $space->setGeoData($geoData);
        $space->setIsValidated(true);

        $admin = $manager->getRepository(User::class)->findOneBy(['email' => 'admin@test.com']);
        if (null !== $admin) {
            $space->setCreatedBy($admin);
        }

        foreach (self::PHOTOS as $fileName) {
            $photo = $this->fileFactory->createMediaObject(
                FixtureFileFactory::IMAGES_DIR.'/divercity/'.$fileName,
                $fileName
            );

            if (null === $photo) {
                continue;
            }

            $manager->persist($photo);
            $space->addPhoto($photo);
        }

        $manager->persist($space);
        $manager->flush();

        $this->addReference(self::REFERENCE_MAIN_SPACE, $space);
    }
}
