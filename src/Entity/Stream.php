<?php

namespace PostChat\Api\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ExistsFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;
use PostChat\Api\Repository\StreamRepository;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get",
 *         "post"={
 *             "denormalization_context"={"groups"={"stream:create"}}
 *         }
 *     },
 *     itemOperations={
 *         "get",
 *         "patch"={
 *             "denormalization_context"={"groups"={"stream:update"}}
 *         }
 *     },
 *     normalizationContext={
 *         "groups"={"stream:read"},
 *         "skip_null_values" = false,
 *     }
 * )
 * @ORM\Entity(repositoryClass=StreamRepository::class)
 * @ORM\Table(name="`stream`", indexes={
 *     @ORM\Index(name="idx_stream_owner", columns={"owner_id"}),
 *     @ORM\Index(name="idx_stream_owner_name", columns={"owner_id", "name"}),
 *     @ORM\Index(name="idx_stream_owner_disc", columns={"owner_id", "discoverable"}),
 *     @ORM\Index(name="idx_stream_owner_disc_name", columns={"owner_id", "discoverable", "name"})
 * })
 * Indexes:
 * - find children streams
 * - find channels versus direct streams
 * - access query
 * - access query with channel/direct query
 */
class Stream
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Groups({"stream:read", "stream:create", "event:read", "event:write", "stream-user:read", "stream-user:write"})
     * @Assert\Uuid
     */
    protected UuidInterface $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"stream:read", "stream:create", "stream:update"})
     * @ApiFilter(ExistsFilter::class)
     */
    public ?string $name;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     * @Groups({"stream:read", "stream:create", "stream:update"})
     */
    public ?string $description;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"stream:read", "stream:create", "stream:update"})
     */
    public bool $discoverable = true;

    /**
     * @ORM\Column(type="boolean")
     */
    public bool $private = false;

    /**
     * @ORM\ManyToOne(targetEntity=Stream::class, inversedBy="subStreams")
     * @Groups({"stream:read", "stream:create"})
     * @ApiFilter(SearchFilter::class, properties={"owner.id": "exact"})
     * @ApiFilter(ExistsFilter::class)
     */
    protected ?Stream $owner = null;

    /**
     * @ORM\OneToMany(targetEntity=Stream::class, mappedBy="owner")
     * @ApiSubresource(maxDepth=1)
     */
    private $subStreams;

    /**
     * @ORM\OneToMany(targetEntity=StreamUser::class, mappedBy="stream", orphanRemoval=true)
     * @ApiSubresource(maxDepth=1)
     */
    private $streamUsers;

    /**
     * @ORM\OneToMany(targetEntity=Event::class, mappedBy="stream", orphanRemoval=true)
     * @ApiSubresource()
     */
    private $events;

    public function __construct()
    {
        $this->subStreams = new ArrayCollection();
        $this->streamUsers = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getOwner(): ?self
    {
        return $this->owner;
    }

    public function setOwner(?self $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return self[]
     */
    public function getSubStreams()
    {
        return $this->subStreams->getValues();
    }

    public function addSubStream(self $subStream): void
    {
        if (!$this->subStreams->contains($subStream)) {
            $this->subStreams[] = $subStream;
            $subStream->setOwner($this);
        }
    }

    public function removeSubStream(self $subStream): void
    {
        if ($this->subStreams->contains($subStream)) {
            $this->subStreams->removeElement($subStream);
            // set the owning side to null (unless already changed)
            if ($subStream->getOwner() === $this) {
                $subStream->setOwner(null);
            }
        }
    }

    /**
     * @return StreamUser[]
     */
    public function getStreamUsers()
    {
        return $this->streamUsers->getValues();
    }

    public function hasUser(User $user): bool
    {
        return (bool)array_filter($this->getStreamUsers(), fn (StreamUser $streamUser) => $streamUser->getUser() === $user);
    }

    public function addStreamUser(StreamUser $streamUser): void
    {
        if (!$this->streamUsers->contains($streamUser)) {
            $this->streamUsers[] = $streamUser;
            $streamUser->setStream($this);
        }

    }

    public function removeStreamUser(StreamUser $streamUser): void
    {
        if ($this->streamUsers->contains($streamUser)) {
            $this->streamUsers->removeElement($streamUser);
        }
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events->getValues();
    }

    public function addEvent(Event $event): void
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setStream($this);
        }
    }

    public function removeEvent(Event $event): void
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
        }
    }
}
