<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Services\State\Provider\ConnectionStatsProvider;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * Statistiques de connexions et d'utilisateurs actifs (cf. ConnectionLog, alimenté par
 * AuthenticationSuccessHandler). GET /api/kpis/connections?days=30, réservé aux admins.
 */
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/kpis/connections',
            provider: ConnectionStatsProvider::class,
            security: 'is_granted("ROLE_ADMIN")',
            normalizationContext: ['groups' => [self::CONNECTION_READ]]
        ),
    ]
)]
class ConnectionStats
{
    public const CONNECTION_READ = 'connection_stats:read';

    #[ApiProperty(identifier: true)]
    #[Groups([self::CONNECTION_READ])]
    private string $id = 'connections';

    #[Groups([self::CONNECTION_READ])]
    private int $totalConnections = 0;

    #[Groups([self::CONNECTION_READ])]
    private int $activeUsers = 0;

    /**
     * @var array<int, array{date: string, count: int}>
     */
    #[Groups([self::CONNECTION_READ])]
    private array $dailySeries = [];

    /**
     * Dernières connexions avec leur provenance (pays + adresse IP).
     *
     * @var array<int, array{connectedAt: string, ipAddress: string|null, countryCode: string|null, countryName: string|null, user: string}>
     */
    #[Groups([self::CONNECTION_READ])]
    private array $recentConnections = [];

    /**
     * Pays qui se connectent le plus à la plateforme, par nombre de connexions.
     *
     * @var array<int, array{countryCode: string|null, countryName: string|null, count: int}>
     */
    #[Groups([self::CONNECTION_READ])]
    private array $topCountries = [];

    public function getId(): string
    {
        return $this->id;
    }

    public function getTotalConnections(): int
    {
        return $this->totalConnections;
    }

    public function setTotalConnections(int $totalConnections): self
    {
        $this->totalConnections = $totalConnections;

        return $this;
    }

    public function getActiveUsers(): int
    {
        return $this->activeUsers;
    }

    public function setActiveUsers(int $activeUsers): self
    {
        $this->activeUsers = $activeUsers;

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

    public function getRecentConnections(): array
    {
        return $this->recentConnections;
    }

    public function setRecentConnections(array $recentConnections): self
    {
        $this->recentConnections = $recentConnections;

        return $this;
    }

    public function getTopCountries(): array
    {
        return $this->topCountries;
    }

    public function setTopCountries(array $topCountries): self
    {
        $this->topCountries = $topCountries;

        return $this;
    }
}
