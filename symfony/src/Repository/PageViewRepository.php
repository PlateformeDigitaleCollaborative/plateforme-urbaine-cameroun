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

    /**
     * Durée moyenne d'une session de consultation ("temps moyen de connexion").
     *
     * Méthode : les pages vues d'un même visiteur (visitorHash) sont découpées en sessions,
     * une nouvelle session démarrant dès qu'il s'écoule plus de $sessionTimeoutMinutes entre
     * deux pages (même convention que les outils d'analytics classiques). La durée d'une
     * session est l'écart entre sa première et sa dernière page vue.
     *
     * Les sessions d'une seule page ("rebonds") ont par construction une durée de 0 et sont
     * exclues du calcul de la moyenne, sans quoi celle-ci serait mécaniquement écrasée. Elles
     * restent comptées dans totalSessions.
     *
     * @return array{averageSeconds: int, totalSessions: int, measurableSessions: int}
     */
    public function getSessionStatsSince(\DateTimeInterface $since, int $sessionTimeoutMinutes = 30): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = <<<'SQL'
            WITH ordered AS (
                SELECT visitor_hash,
                       viewed_at,
                       LAG(viewed_at) OVER (PARTITION BY visitor_hash ORDER BY viewed_at) AS previous_viewed_at
                FROM page_view
                WHERE viewed_at >= :since
            ),
            flagged AS (
                SELECT visitor_hash,
                       viewed_at,
                       CASE
                           WHEN previous_viewed_at IS NULL
                                OR viewed_at - previous_viewed_at > make_interval(mins => CAST(:timeout AS int))
                           THEN 1
                           ELSE 0
                       END AS is_new_session
                FROM ordered
            ),
            numbered AS (
                SELECT visitor_hash,
                       viewed_at,
                       SUM(is_new_session) OVER (
                           PARTITION BY visitor_hash ORDER BY viewed_at ROWS UNBOUNDED PRECEDING
                       ) AS session_number
                FROM flagged
            ),
            sessions AS (
                SELECT visitor_hash,
                       session_number,
                       COUNT(*) AS views,
                       EXTRACT(EPOCH FROM (MAX(viewed_at) - MIN(viewed_at))) AS duration_seconds
                FROM numbered
                GROUP BY visitor_hash, session_number
            )
            SELECT COUNT(*) AS total_sessions,
                   COUNT(*) FILTER (WHERE views > 1) AS measurable_sessions,
                   COALESCE(AVG(duration_seconds) FILTER (WHERE views > 1), 0) AS average_seconds
            FROM sessions
        SQL;

        $row = $connection->executeQuery($sql, [
            'since' => $since->format('Y-m-d H:i:s'),
            'timeout' => $sessionTimeoutMinutes,
        ])->fetchAssociative();

        if (false === $row) {
            return ['averageSeconds' => 0, 'totalSessions' => 0, 'measurableSessions' => 0];
        }

        return [
            'averageSeconds' => (int) round((float) $row['average_seconds']),
            'totalSessions' => (int) $row['total_sessions'],
            'measurableSessions' => (int) $row['measurable_sessions'],
        ];
    }
}
