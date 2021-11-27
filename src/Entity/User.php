<?php

namespace EventStreamApi\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use EventStreamApi\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={"get", "patch"={"security"="object == user"}},
 *     normalizationContext={
 *         "groups"={"user:read"},
 *         "skip_null_values" = false
 *     },
 *     denormalizationContext={},
 *     attributes={}
 * )
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", unique=true)
     * @Groups({"user:read", "stream-user:create", "stream-user:read", "event:read"})
     */
    protected string $id;

    /**
     * @ORM\OneToMany(targetEntity=StreamUser::class, mappedBy="user", orphanRemoval=true)
     * @var Collection<int, StreamUser>|StreamUser[]
     */
    private $streamUsers;

    /**
     * @ORM\OneToMany(targetEntity=Event::class, mappedBy="user", orphanRemoval=true)
     * @var Collection<int, Event>|Event[]
     */
    private $events;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->streamUsers = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getStreamUserForStream(Stream $stream): ?StreamUser
    {
        foreach($this->streamUsers as $streamUser) {
            if($streamUser->getStream()->getId() === $stream->getId()) {
                return $streamUser;
            }
        }

        return null;
    }

    /**
     * @return StreamUser[]
     */
    public function getStreamUsers(): array
    {
        return $this->streamUsers->toArray();
    }

    public function addStreamUser(StreamUser $streamUser): void
    {
        if (!$this->streamUsers->contains($streamUser)) {
            $this->streamUsers[] = $streamUser;
            $streamUser->setUser($this);
        }
    }

    public function removeStreamUser(StreamUser $streamUser): void
    {
        if ($this->streamUsers->contains($streamUser)) {
            $this->streamUsers->removeElement($streamUser);
        }
    }

    /**
     * @return Event[]
     */
    public function getEvents(): array
    {
        return $this->events->toArray();
    }

    public function addEvent(Event $event): void
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setUser($this);
        }
    }

    public function removeEvent(Event $event): void
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
        }
    }

    public function getRoles(): array
    {
        return [];
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getUserIdentifier(): string
    {
        return $this->id;
    }

    public function eraseCredentials(): void
    {
    }
}
