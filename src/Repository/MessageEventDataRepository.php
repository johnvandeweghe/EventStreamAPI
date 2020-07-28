<?php

namespace PostChat\Api\Repository;

use PostChat\Api\Entity\MessageEventData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MessageEventData|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageEventData|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageEventData[]    findAll()
 * @method MessageEventData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageEventDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageEventData::class);
    }
}
