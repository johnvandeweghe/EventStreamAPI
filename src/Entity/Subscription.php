<?php

namespace Productively\Api\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Ramsey\Uuid\UuidInterface;

/**
 * @ApiResource(
 *     collectionOperations={"get","post"},
 *     itemOperations={"get", "delete"},
 *     normalizationContext={"groups"={"subscription:read"}},
 *     denormalizationContext={"groups"={"subscription:write"}}
 * )
 * @ORM\Entity(repositoryClass="Productively\Api\Repository\SubscriptionRepository")
 */
class Subscription
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Groups({"subscription:read"})
     */
    protected UuidInterface $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"subscription:read", "subscription:write"})
     */
    public string $transport;

    /**
     * @ORM\ManyToOne(targetEntity="Productively\Api\Entity\GroupMember", inversedBy="subscriptions")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"subscription:read", "subscription:write"})
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
