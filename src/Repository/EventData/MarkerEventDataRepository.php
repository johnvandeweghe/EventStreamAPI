<?php

namespace EventStreamApi\Repository\EventData;

use EventStreamApi\Entity\EventData\MarkerEventData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MarkerEventData|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkerEventData|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkerEventData[]    findAll()
 * @method MarkerEventData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkerEventDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkerEventData::class);
    }
}
