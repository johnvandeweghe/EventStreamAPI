<?php

namespace Productively\Api\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Ramsey\Uuid\UuidInterface;

/**
 * @ApiResource(
 *     collectionOperations={"get","post"},
 *     itemOperations={"get", "delete"},
 *     normalizationContext={"groups"={"group-member:read"}},
 *     denormalizationContext={"groups"={"group-member:write"}}
 * )
 * @ORM\Entity(repositoryClass="Productively\Api\Repository\GroupMemberRepository")
 */
class GroupMember
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Groups({"group-member:read", "subscription:read", "subscription:write"})
     */
    protected UuidInterface $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"group-member:read", "group-member:write"})
     * @ApiFilter(SearchFilter::class, strategy="exact")
     */
    protected $userIdentifer;

    /**
     * @ORM\ManyToOne(targetEntity="Productively\Api\Entity\Group", inversedBy="groupMembers")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"group-member:read", "group-member:write"})
     */
    protected $userGroup;

    /**
     * @ORM\OneToMany(targetEntity="Productively\Api\Entity\Subscription", mappedBy="groupMember", orphanRemoval=true)
     * @ApiSubresource()
     */
    protected $subscriptions;

    public function __construct()
    {
        $this->subscriptions = new ArrayCollection();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getUserIdentifer(): ?string
    {
        return $this->userIdentifer;
    }

    public function setUserIdentifer(string $userIdentifer): self
    {
        $this->userIdentifer = $userIdentifer;

        return $this;
    }

    public function getUserGroup(): ?Group
    {
        return $this->userGroup;
    }

    public function setUserGroup(?Group $userGroup): self
    {
        $this->userGroup = $userGroup;

        return $this;
    }

    /**
     * @return Collection|Subscription[]
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(Subscription $subscription): self
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions[] = $subscription;
            $subscription->setGroupMember($this);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): self
    {
        if ($this->subscriptions->contains($subscription)) {
            $this->subscriptions->removeElement($subscription);
            // set the owning side to null (unless already changed)
            if ($subscription->getGroupMember() === $this) {
                $subscription->setGroupMember(null);
            }
        }

        return $this;
    }
}
