<?php

namespace PostChat\Api\Repository;

use PostChat\Api\Entity\CommandEventData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CommandEventData|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommandEventData|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommandEventData[]    findAll()
 * @method CommandEventData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandEventDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandEventData::class);
    }
}
