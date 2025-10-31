<?php

namespace DailyCheckinBundle\Repository;

use DailyCheckinBundle\Entity\Activity;
use DailyCheckinBundle\Entity\Record;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<Record>
 */
#[AsRepository(entityClass: Record::class)]
class RecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Record::class);
    }

    public function save(Record $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Record $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 获取用户签到记录（优化版，避免N+1查询）
     *
     * @param Activity $activity 活动实体
     * @param mixed $user 用户实体
     * @return array<int, Record>
     */
    public function findByActivityAndUserWithJoins(Activity $activity, $user): array
    {
        $result = $this->createQueryBuilder('r')
            ->leftJoin('r.awards', 'a')
            ->leftJoin('r.activity', 'act')
            ->leftJoin('r.user', 'u')
            ->addSelect('a', 'act', 'u')
            ->where('r.activity = :activity')
            ->andWhere('r.user = :user')
            ->setParameter('activity', $activity)
            ->setParameter('user', $user)
            ->orderBy('r.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        assert(is_array($result));
        /** @var array<int, Record> $result */

        return $result;
    }

    /**
     * 获取最近签到记录（优化版，避免N+1查询）
     *
     * @param Activity $activity 活动实体
     * @param int $limit 限制数量
     * @return array<int, Record>
     */
    public function findRecentRecordsWithJoins(Activity $activity, int $limit = 10): array
    {
        $result = $this->createQueryBuilder('r')
            ->leftJoin('r.awards', 'a')
            ->leftJoin('r.user', 'u')
            ->addSelect('a', 'u')
            ->where('r.activity = :activity')
            ->setParameter('activity', $activity)
            ->orderBy('r.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        assert(is_array($result));
        /** @var array<int, Record> $result */

        return $result;
    }
}
