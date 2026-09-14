<?php

namespace App\Services\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\AudienceStats;
use App\Repository\PageViewRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class AudienceStatsProvider implements ProviderInterface
{
    public function __construct(
        private readonly PageViewRepository $pageViewRepository,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AudienceStats
    {
        $days = (int) ($this->requestStack->getCurrentRequest()?->query->get('days') ?? 30);
        $since = (new \DateTimeImmutable())->modify(sprintf('-%d days', max(1, $days)));

        $stats = new AudienceStats();
        $stats->setTotalViews($this->pageViewRepository->countViewsSince($since));
        $stats->setUniqueVisitors($this->pageViewRepository->countUniqueVisitorsSince($since));
        $stats->setDailySeries($this->pageViewRepository->countViewsGroupedByDay($since));
        $stats->setTopPages($this->pageViewRepository->getTopPathsSince($since, null, 10));

        return $stats;
    }
}