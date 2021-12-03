<?php

namespace EventStreamApi\Repository;

use EventStreamApi\Entity\StreamUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StreamUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method StreamUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method StreamUser[]    findAll()
 * @method StreamUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @template-extends ServiceEntityRepository<StreamUser>
 */
class StreamUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StreamUser::class);
    }
}
