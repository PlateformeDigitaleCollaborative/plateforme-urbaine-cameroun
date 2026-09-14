<?php

namespace App\Services\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\ConnectionStats;
use App\Repository\ConnectionLogRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class ConnectionStatsProvider implements ProviderInterface
{
    public function __construct(
        private readonly ConnectionLogRepository $connectionLogRepository,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ConnectionStats
    {
        $days = (int) ($this->requestStack->getCurrentRequest()?->query->get('days') ?? 30);
        $since = (new \DateTimeImmutable())->modify(sprintf('-%d days', max(1, $days)));

        $stats = new ConnectionStats();
        $stats->setTotalConnections($this->connectionLogRepository->countConnectionsSince($since));
        $stats->setActiveUsers($this->connectionLogRepository->countDistinctUsersSince($since));
        $stats->setDailySeries($this->connectionLogRepository->countConnectionsGroupedByDay($since));

        return $stats;
    }
}