<?php

namespace EventStreamApi\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use EventStreamApi\Entity\WebhookSubscriptionData;

/**
 * @method WebhookSubscriptionData|null find($id, $lockMode = null, $lockVersion = null)
 * @method WebhookSubscriptionData|null findOneBy(array $criteria, array $orderBy = null)
 * @method WebhookSubscriptionData[]    findAll()
 * @method WebhookSubscriptionData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WebhookSubscriptionDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebhookSubscriptionData::class);
    }
}
