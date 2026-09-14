<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Services\State\Provider\AudienceStatsProvider;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * Statistiques d'audience du site PDC (tracking interne, cf. PageView) : nombre de
 * vues et de visiteurs uniques, courbe par jour, pages les plus consultées.
 * GET /api/kpis/audience?days=30 (par défaut 30 jours), réservé aux admins.
 */
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/kpis/audience',
            provider: AudienceStatsProvider::class,
            security: 'is_granted("ROLE_ADMIN")',
            normalizationContext: ['groups' => [self::AUDIENCE_READ]]
        ),
    ]
)]
class AudienceStats
{
    public const AUDIENCE_READ = 'audience_stats:read';

    #[ApiProperty(identifier: true)]
    #[Groups([self::AUDIENCE_READ])]
    private string $id = 'audience';

    #[Groups([self::AUDIENCE_READ])]
    private int $totalViews = 0;

    #[Groups([self::AUDIENCE_READ])]
    private int $uniqueVisitors = 0;

    /**
     * @var array<int, array{date: string, views: int, uniqueVisitors: int}>
     */
    #[Groups([self::AUDIENCE_READ])]
    private array $dailySeries = [];

    /**
     * @var array<int, array{path: string, views: int}>
     */
    #[Groups([self::AUDIENCE_READ])]
    private array $topPages = [];

    public function getId(): string
    {
        return $this->id;
    }

    public function getTotalViews(): int
    {
        return $this->totalViews;
    }

    public function setTotalViews(int $totalViews): self
    {
        $this->totalViews = $totalViews;

        return $this;
    }

    public function getUniqueVisitors(): int
    {
        return $this->uniqueVisitors;
    }

    public function setUniqueVisitors(int $uniqueVisitors): self
    {
        $this->uniqueVisitors = $uniqueVisitors;

        return $this;
    }

    public function getDailySeries(): array
    {
        return $this->dailySeries;
    }

    public function setDailySeries(array $dailySeries): self
    {
        $this->dailySeries = $dailySeries;

        return $this;
    }

    public function getTopPages(): array
    {
        return $this->topPages;
    }

    public function setTopPages(array $topPages): self
    {
        $this->topPages = $topPages;

        return $this;
    }
}