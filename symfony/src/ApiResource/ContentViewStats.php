<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Services\State\Provider\ContentViewStatsProvider;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * Fiches Acteur et Projet les plus consultées (cf. PageView, filtré par préfixe de
 * chemin /acteurs/ et /projets/). GET /api/kpis/content-views?days=30&limit=10,
 * réservé aux admins.
 */
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/kpis/content-views',
            provider: ContentViewStatsProvider::class,
            security: 'is_granted("ROLE_ADMIN")',
            normalizationContext: ['groups' => [self::CONTENT_VIEW_READ]]
        ),
    ]
)]
class ContentViewStats
{
    public const CONTENT_VIEW_READ = 'content_view_stats:read';

    #[ApiProperty(identifier: true)]
    #[Groups([self::CONTENT_VIEW_READ])]
    private string $id = 'content-views';

    /**
     * @var array<int, array{path: string, slug: string, views: int}>
     */
    #[Groups([self::CONTENT_VIEW_READ])]
    private array $topActors = [];

    /**
     * @var array<int, array{path: string, slug: string, views: int}>
     */
    #[Groups([self::CONTENT_VIEW_READ])]
    private array $topProjects = [];

    public function getId(): string
    {
        return $this->id;
    }

    public function getTopActors(): array
    {
        return $this->topActors;
    }

    public function setTopActors(array $topActors): self
    {
        $this->topActors = $topActors;

        return $this;
    }

    public function getTopProjects(): array
    {
        return $this->topProjects;
    }

    public function setTopProjects(array $topProjects): self
    {
        $this->topProjects = $topProjects;

        return $this;
    }
}
