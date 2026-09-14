<?php

namespace App\Services\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\ContentViewStats;
use App\Repository\PageViewRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class ContentViewStatsProvider implements ProviderInterface
{
    // NB : correspond aux segments de route traduits (src/assets/translations/fr/routes.json
    // routes.actors = "acteurs", routes.projects = "projets"). Le site n'étant qu'en français
    // pour l'instant, ces préfixes sont fixes ; à revoir si une autre langue est ajoutée un jour.
    private const ACTOR_PATH_PREFIX = '/acteurs/';
    private const PROJECT_PATH_PREFIX = '/projets/';

    public function __construct(
        private readonly PageViewRepository $pageViewRepository,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ContentViewStats
    {
        $request = $this->requestStack->getCurrentRequest();
        $days = (int) ($request?->query->get('days') ?? 30);
        $limit = (int) ($request?->query->get('limit') ?? 10);
        $since = (new \DateTimeImmutable())->modify(sprintf('-%d days', max(1, $days)));

        $stats = new ContentViewStats();
        $stats->setTopActors($this->withSlugs($this->pageViewRepository->getTopPathsSince($since, self::ACTOR_PATH_PREFIX, $limit), self::ACTOR_PATH_PREFIX));
        $stats->setTopProjects($this->withSlugs($this->pageViewRepository->getTopPathsSince($since, self::PROJECT_PATH_PREFIX, $limit), self::PROJECT_PATH_PREFIX));

        return $stats;
    }

    /**
     * @param array<int, array{path: string, views: int}> $rows
     *
     * @return array<int, array{path: string, slug: string, views: int}>
     */
    private function withSlugs(array $rows, string $prefix): array
    {
        return array_map(static fn (array $row) => [
            'path' => $row['path'],
            'slug' => substr($row['path'], strlen($prefix)),
            'views' => $row['views'],
        ], $rows);
    }
}