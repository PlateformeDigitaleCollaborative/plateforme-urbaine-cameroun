<?php

namespace App\Repository;

use App\Entity\PageView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PageView>
 */
class PageViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageView::class);
    }

    public function logView(string $path, string $visitorHash): void
    {
        $pageView = (new PageView())
            ->setPath($path)
            ->setVisitorHash($visitorHash)
            ->setViewedAt(new \DateTimeImmutable());

        $entityManager = $this->getEntityManager();
        $entityManager->persist($pageView);
        $entityManager->flush();
    }

    public function countViewsSince(\DateTimeInterface $since): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.viewedAt >= :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countUniqueVisitorsSince(\DateTimeInterface $since): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p.visitorHash)')
            ->where('p.viewedAt >= :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Vues totales et visiteurs uniques par jour depuis $since, pour tracer des courbes.
     *
     * @return array<int, array{date: string, views: int, uniqueVisitors: int}>
     */
    public function countViewsGroupedByDay(\DateTimeInterface $since): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = <<<'SQL'
            SELECT date_trunc('day', viewed_at)::date AS day,
                   COUNT(*) AS views,
                   COUNT(DISTINCT visitor_hash) AS unique_visitors
            FROM page_view
            WHERE viewed_at >= :since
            GROUP BY day
            ORDER BY day ASC
        SQL;

        $rows = $connection->executeQuery($sql, [
            'since' => $since->format('Y-m-d H:i:s'),
        ])->fetchAllAssociative();

        return array_map(static fn (array $row) => [
            'date' => $row['day'],
            'views' => (int) $row['views'],
            'uniqueVisitors' => (int) $row['unique_visitors'],
        ], $rows);
    }

    /**
     * Pages les plus consultées depuis $since, filtrées sur un préfixe de chemin
     * (ex: "/acteurs/" ou "/projets/" pour les stats de fiches).
     *
     * @return array<int, array{path: string, views: int}>
     */
    public function getTopPathsSince(\DateTimeInterface $since, ?string $pathPrefix = null, int $limit = 10): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p.path AS path', 'COUNT(p.id) AS views')
            ->where('p.viewedAt >= :since')
            ->setParameter('since', $since)
            ->groupBy('p.path')
            ->orderBy('views', 'DESC')
            ->setMaxResults($limit);

        if (null !== $pathPrefix) {
            $queryBuilder
                ->andWhere('p.path LIKE :pathPrefix')
                ->setParameter('pathPrefix', $pathPrefix.'%');
        }

        $rows = $queryBuilder->getQuery()->getResult();

        return array_map(static fn (array $row) => [
            'path' => $row['path'],
            'views' => (int) $row['views'],
        ], $rows);
    }

    /**
     * Historique jour par jour des vues d'une fiche précise (un seul path), pour
     * afficher la courbe de fréquentation d'un Acteur ou Projet donné.
     *
     * @return array<int, array{date: string, views: int}>
     */
    public function countViewsGroupedByDayForPath(string $path, \DateTimeInterface $since): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = <<<'SQL'
            SELECT date_trunc('day', viewed_at)::date AS day, COUNT(*) AS views
            FROM page_view
            WHERE viewed_at >= :since AND path = :path
            GROUP BY day
            ORDER BY day ASC
        SQL;

        $rows = $connection->executeQuery($sql, [
            'since' => $since->format('Y-m-d H:i:s'),
            'path' => $path,
        ])->fetchAllAssociative();

        return array_map(static fn (array $row) => [
            'date' => $row['day'],
            'views' => (int) $row['views'],
        ], $rows);
    }
}
