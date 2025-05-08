<?php

namespace DailyCheckinBundle\Repository;

use DailyCheckinBundle\Entity\Record;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method Record|null find($id, $lockMode = null, $lockVersion = null)
 * @method Record|null findOneBy(array $criteria, array $orderBy = null)
 * @method Record[]    findAll()
 * @method Record[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecordRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Record::class);
    }
}
