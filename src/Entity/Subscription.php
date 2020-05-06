<?php

namespace Productively\Api\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="Productively\Api\Repository\SubscriptionRepository")
 */
class Subscription
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    protected UuidInterface $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public string $transport;

    /**
     * @ORM\ManyToOne(targetEntity="Productively\Api\Entity\GroupMember", inversedBy="subscriptions")
     * @ORM\JoinColumn(nullable=false)
     */
    protected GroupMember $groupMember;

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getGroupMember(): GroupMember
    {
        return $this->groupMember;
    }

    public function setGroupMember(GroupMember $groupMember): self
    {
        $this->groupMember = $groupMember;

        return $this;
    }
}
