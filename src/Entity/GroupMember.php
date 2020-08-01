<?php

namespace PostChat\Api\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={"get","post"},
 *     itemOperations={
 *         "get",
 *         "patch"={"security"="object.user == user"},
 *         "delete"={"security"="object.user == user"}
 *     },
 *     normalizationContext={
 *         "groups"={"group-member:read"},
 *         "skip_null_values" = false
 *     },
 *     denormalizationContext={"groups"={"group-member:write"}}
 * )
 * @ORM\Entity(repositoryClass="PostChat\Api\Repository\GroupMemberRepository")
 * @ORM\Table(
 *     uniqueConstraints={@ORM\UniqueConstraint(name="uq_groupmembership", columns={"user_id", "user_group_id"})},
 *     indexes={
 *       @ORM\Index(name="idx_group_id", columns={"user_group_id"}),
 *       @ORM\Index(name="idx_user_id", columns={"user_id"}),
 *       @ORM\Index(name="idx_user_group", columns={"user_id", "user_group_id"})
 *     }
 * )
 */
class GroupMember
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Groups({"group-member:read", "subscription:read", "subscription:write"})
     * @Assert\Uuid
     */
    protected UuidInterface $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="groupMembers")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"group-member:read", "group-member:write"})
     * @ApiFilter(SearchFilter::class, properties={"user.id": "exact"})
     * @ApiProperty(push=true)
     */
    protected User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Group::class, inversedBy="groupMembers")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"group-member:read", "group-member:write"})
     * @ApiFilter(SearchFilter::class, properties={"userGroup.id": "exact"})
     */
    protected $userGroup;

    /**
     * @ORM\OneToMany(targetEntity=Subscription::class, mappedBy="groupMember", orphanRemoval=true)
     * @ApiSubresource()
     */
    protected $subscriptions;

    /**
     * @ORM\ManyToOne(targetEntity=Event::class)
     * @Groups({"group-member:read", "group-member:write"})
     */
    protected $lastSeenEvent;

    public function __construct()
    {
        $this->subscriptions = new ArrayCollection();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getUserGroup(): Group
    {
        return $this->userGroup;
    }

    public function setUserGroup(Group $userGroup): void
    {
        $this->userGroup = $userGroup;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return Collection|Subscription[]
     */
    public function getSubscriptions()
    {
        return $this->subscriptions->getValues();
    }

    public function addSubscription(Subscription $subscription): void
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions[] = $subscription;
            $subscription->setGroupMember($this);
        }
    }

    public function removeSubscription(Subscription $subscription): void
    {
        if ($this->subscriptions->contains($subscription)) {
            $this->subscriptions->removeElement($subscription);
            // set the owning side to null (unless already changed)
            if ($subscription->getGroupMember() === $this) {
                $subscription->setGroupMember(null);
            }
        }
    }

    public function getLastSeenEvent(): ?Event
    {
        return $this->lastSeenEvent;
    }

    public function setLastSeenEvent(?Event $lastSeenEvent): void
    {
        $this->lastSeenEvent = $lastSeenEvent;
    }
}
