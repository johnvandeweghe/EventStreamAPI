<?php

namespace Productively\Api\Repository;

use Productively\Api\Entity\MessageEventData;
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

    // /**
    //  * @return MessageEventData[] Returns an array of MessageEventData objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MessageEventData
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
