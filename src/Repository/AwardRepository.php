<?php

namespace DailyCheckinBundle\Repository;

use DailyCheckinBundle\Entity\Award;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method Award|null find($id, $lockMode = null, $lockVersion = null)
 * @method Award|null findOneBy(array $criteria, array $orderBy = null)
 * @method Award[]    findAll()
 * @method Award[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AwardRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Award::class);
    }
}
