<?php

namespace PostChat\Api\Repository;

use PostChat\Api\Entity\GroupMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GroupMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupMember[]    findAll()
 * @method GroupMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupMember::class);
    }
}
