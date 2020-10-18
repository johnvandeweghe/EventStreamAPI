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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;
use PostChat\Api\Repository\StreamUserRepository;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get",
 *         "post"={
 *             "security_post_denormalize"="is_granted('STREAM_JOIN', object)",
 *             "denormalization_context"={"groups"={"stream-user:create"}}
 *         }
 *     },
 *     itemOperations={
 *         "get",
 *         "patch"={"security"="object.getUser() == user"},
 *         "delete"={"security"="object.getUser() == user"}
 *     },
 *     normalizationContext={
 *         "groups"={"stream-user:read"},
 *         "skip_null_values" = false
 *     },
 *     denormalizationContext={"groups"={"stream-user:write"}}
 * )
 * @ORM\Entity(repositoryClass=StreamUserRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={@ORM\UniqueConstraint(name="uq_stream_user", columns={"user_id", "stream_id"})},
 *     indexes={
 *       @ORM\Index(name="idx_su_stream_id", columns={"stream_id"}),
 *       @ORM\Index(name="idx_su_user_id", columns={"user_id"}),
 *       @ORM\Index(name="idx_su_user_stream", columns={"user_id", "stream_id"})
 *     }
 * )
 * @UniqueEntity(fields={"user", "stream"})
 */
class StreamUser
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Groups({"stream-user:read", "subscription:read", "subscription:write"})
     * @Assert\Uuid
     */
    protected UuidInterface $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="streamUsers")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"stream-user:read", "stream-user:create"})
     * @ApiFilter(SearchFilter::class, properties={"user.id": "exact"})
     * @ApiProperty(push=true)
     */
    protected User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Stream::class, inversedBy="streamUsers")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"stream-user:read", "stream-user:create"})
     * @ApiFilter(SearchFilter::class, properties={"stream.id": "exact"})
     */
    protected Stream $stream;

    /**
     * @ORM\OneToMany(targetEntity=Subscription::class, mappedBy="streamUser", orphanRemoval=true)
     * @ApiSubresource()
     */
    protected $subscriptions;

    /**
     * @ORM\ManyToOne(targetEntity=Event::class)
     * @Groups({"stream-user:read", "stream-user:write"})
     */
    protected ?Event $lastSeenEvent;

    /**
     * @ORM\ManyToMany(targetEntity=Role::class)
     * @Groups({"stream-user:read"})
     */
    protected $roles;

    /**
     * @ORM\OneToOne(targetEntity=Invite::class, inversedBy="invitedStreamUser", cascade={"persist", "remove"})
     * @Groups({"stream-user:create"})
     */
    protected ?Invite $invite = null;

    public function __construct()
    {
        $this->subscriptions = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getStream(): Stream
    {
        return $this->stream;
    }

    public function setStream(Stream $stream): void
    {
        $this->stream = $stream;
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
            $subscription->setStreamUser($this);
        }
    }

    public function removeSubscription(Subscription $subscription): void
    {
        if ($this->subscriptions->contains($subscription)) {
            $this->subscriptions->removeElement($subscription);
            // set the owning side to null (unless already changed)
            if ($subscription->getStreamUser() === $this) {
                $subscription->setStreamUser(null);
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

    public function hasPermission(string $permission): bool
    {
        foreach($this->getRoles() as $role) {
            if($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection|Role[]
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }

        return $this;
    }

    public function getInvite(): ?Invite
    {
        return $this->invite;
    }

    public function setInvite(?Invite $invite): void
    {
        $this->invite = $invite;
    }
}
