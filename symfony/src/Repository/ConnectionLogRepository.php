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

    public function logConnection(User $user, ?string $ipAddress = null): ConnectionLog
    {
        $connectionLog = (new ConnectionLog())
            ->setUser($user)
            ->setConnectedAt(new \DateTimeImmutable())
            ->setIpAddress($ipAddress);

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
}
