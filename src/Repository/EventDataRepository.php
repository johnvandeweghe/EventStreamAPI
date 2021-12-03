<?php

namespace EventStreamApi\Repository;

use EventStreamApi\Entity\EventData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EventData|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventData|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventData[]    findAll()
 * @method EventData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @template-extends ServiceEntityRepository<EventData>
 */
class EventDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventData::class);
    }
}
