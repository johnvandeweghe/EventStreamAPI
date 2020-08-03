<?php

namespace PostChat\Api\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PostChat\Api\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={"get", "patch"={"security"="object.user == user"}},
 *     normalizationContext={
 *         "groups"={"user:read"},
 *         "skip_null_values" = false
 *     },
 *     denormalizationContext={"groups"={"user:write"}},
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
     * @Groups({"user:read", "group-member:write", "group-member:read", "event:read"})
     */
    protected string $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user:read", "user:write"})
     */
    public ?string $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user:read", "user:write"})
     * @Assert\Email()
     */
    public ?string $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user:read", "user:write"})
     */
    public ?string $picture;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user:read", "user:write"})
     */
    public ?string $nickname;

    /**
     * @ORM\OneToMany(targetEntity=GroupMember::class, mappedBy="user", orphanRemoval=true)
     */
    private $groupMembers;

    /**
     * @ORM\OneToMany(targetEntity=Event::class, mappedBy="user", orphanRemoval=true)
     */
    private $events;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->groupMembers = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return GroupMember[]
     */
    public function getGroupMembers()
    {
        return $this->groupMembers->getValues();
    }

    public function addGroupMember(GroupMember $groupMember): void
    {
        if (!$this->groupMembers->contains($groupMember)) {
            $this->groupMembers[] = $groupMember;
            $groupMember->setUser($this);
        }
    }

    public function removeGroupMember(GroupMember $groupMember): void
    {
        if ($this->groupMembers->contains($groupMember)) {
            $this->groupMembers->removeElement($groupMember);
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
        return $this->id;
    }

    public function eraseCredentials(): void
    {
    }
}
