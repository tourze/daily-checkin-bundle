<?php

namespace DailyCheckinBundle\Repository;

use DailyCheckinBundle\Entity\Reward;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method Reward|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reward|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reward[]    findAll()
 * @method Reward[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RewardRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reward::class);
    }
}
