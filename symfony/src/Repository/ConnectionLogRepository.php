<?php

namespace App\Repository;

use App\Entity\ConnectionLog;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConnectionLog>
 */
class ConnectionLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConnectionLog::class);
    }

    public function logConnection(
        User $user,
        ?string $ipAddress = null,
        ?string $countryCode = null,
        ?string $countryName = null,
    ): ConnectionLog {
        $connectionLog = (new ConnectionLog())
            ->setUser($user)
            ->setConnectedAt(new \DateTimeImmutable())
            ->setIpAddress($ipAddress)
            ->setCountryCode($countryCode)
            ->setCountryName($countryName);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($connectionLog);
        $entityManager->flush();

        return $connectionLog;
    }

    public function countConnectionsSince(\DateTimeInterface $since): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.connectedAt >= :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countDistinctUsersSince(\DateTimeInterface $since): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(DISTINCT c.user)')
            ->where('c.connectedAt >= :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Nombre de connexions par jour depuis $since, pour tracer une courbe.
     *
     * @return array<int, array{date: string, count: int}>
     */
    public function countConnectionsGroupedByDay(\DateTimeInterface $since): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = <<<'SQL'
            SELECT date_trunc('day', connected_at)::date AS day, COUNT(*) AS count
            FROM connection_log
            WHERE connected_at >= :since
            GROUP BY day
            ORDER BY day ASC
        SQL;

        $rows = $connection->executeQuery($sql, [
            'since' => $since->format('Y-m-d H:i:s'),
        ])->fetchAllAssociative();

        return array_map(static fn (array $row) => [
            'date' => $row['day'],
            'count' => (int) $row['count'],
        ], $rows);
    }

    /**
     * Dernières connexions enregistrées, avec leur provenance (pays + adresse IP).
     * Sert au tableau "Provenance des connexions" du panneau d'administration.
     *
     * @return array<int, array{connectedAt: string, ipAddress: string|null, countryCode: string|null, countryName: string|null, user: string}>
     */
    public function getRecentConnections(\DateTimeInterface $since, int $limit = 20): array
    {
        $rows = $this->createQueryBuilder('c')
            ->select(
                'c.connectedAt AS connectedAt',
                'c.ipAddress AS ipAddress',
                'c.countryCode AS countryCode',
                'c.countryName AS countryName',
                'u.firstName AS firstName',
                'u.lastName AS lastName',
                'u.email AS email'
            )
            ->join('c.user', 'u')
            ->where('c.connectedAt >= :since')
            ->setParameter('since', $since)
            ->orderBy('c.connectedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_map(static function (array $row): array {
            $fullName = trim(sprintf('%s %s', $row['firstName'] ?? '', $row['lastName'] ?? ''));

            return [
                'connectedAt' => $row['connectedAt'] instanceof \DateTimeInterface
                    ? $row['connectedAt']->format(\DateTimeInterface::ATOM)
                    : (string) $row['connectedAt'],
                'ipAddress' => $row['ipAddress'],
                'countryCode' => $row['countryCode'],
                'countryName' => $row['countryName'],
                'user' => '' !== $fullName ? $fullName : (string) $row['email'],
            ];
        }, $rows);
    }

    /**
     * Pays d'où proviennent le plus de connexions depuis $since.
     * Les connexions sans pays résolu (IP privée, géolocalisation indisponible) sont
     * regroupées avec un countryCode null et affichées comme "Inconnu" côté frontend.
     *
     * @return array<int, array{countryCode: string|null, countryName: string|null, count: int}>
     */
    public function countConnectionsGroupedByCountry(\DateTimeInterface $since, int $limit = 10): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = <<<'SQL'
            SELECT country_code, MAX(country_name) AS country_name, COUNT(*) AS count
            FROM connection_log
            WHERE connected_at >= :since
            GROUP BY country_code
            ORDER BY count DESC, country_code ASC
            LIMIT :limit
        SQL;

        $rows = $connection->executeQuery(
            $sql,
            [
                'since' => $since->format('Y-m-d H:i:s'),
                'limit' => $limit,
            ],
            [
                'limit' => \PDO::PARAM_INT,
            ]
        )->fetchAllAssociative();

        return array_map(static fn (array $row) => [
            'countryCode' => $row['country_code'],
            'countryName' => $row['country_name'],
            'count' => (int) $row['count'],
        ], $rows);
    }
}
