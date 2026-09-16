<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Repository\PageViewRepository;
use App\Services\State\Processor\PageView\PageViewLogDto;
use App\Services\State\Processor\PageView\PageViewLogProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Une ligne = une page vue côté frontend (SPA), envoyée par le router Vue à chaque navigation.
 * Remplace l'usage de GoatCounter pour les KPIs internes de la PDC (tracking 100% interne,
 * sans dépendance/coût externe).
 *
 * Vie privée : on ne stocke jamais l'IP ni le User-Agent en clair, seulement un hash
 * (visitorHash) calculé à partir de IP + User-Agent + jour, qui permet de compter des
 * "visiteurs uniques" par jour sans conserver de donnée personnelle identifiable.
 */
#[ORM\Entity(repositoryClass: PageViewRepository::class)]
#[ORM\Index(columns: ['viewed_at'], name: 'idx_page_view_viewed_at')]
#[ORM\Index(columns: ['path'], name: 'idx_page_view_path')]
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/page_views',
            input: PageViewLogDto::class,
            processor: PageViewLogProcessor::class,
            output: false,
            status: 204,
            security: "is_granted('PUBLIC_ACCESS')" // suivi anonyme, ouvert à tout visiteur du site public
        ),
    ]
)]
class PageView
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500)]
    private ?string $path = null;

    #[ORM\Column(length: 64)]
    private ?string $visitorHash = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $viewedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getVisitorHash(): ?string
    {
        return $this->visitorHash;
    }

    public function setVisitorHash(string $visitorHash): static
    {
        $this->visitorHash = $visitorHash;

        return $this;
    }

    public function getViewedAt(): ?\DateTimeImmutable
    {
        return $this->viewedAt;
    }

    public function setViewedAt(\DateTimeImmutable $viewedAt): static
    {
        $this->viewedAt = $viewedAt;

        return $this;
    }
}
