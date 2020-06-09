<?php

namespace Productively\Api\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={"get","post"},
 *     itemOperations={"get"},
 *     normalizationContext={
 *         "groups"={"group:read"},
 *         "skip_null_values" = false,
 *     },
 *     denormalizationContext={"groups"={"group:write"}}
 * )
 * @ORM\Entity(repositoryClass="Productively\Api\Repository\GroupRepository")
 * @ORM\Table(name="`group`")
 */
class Group
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Groups({"group:read", "group:write", "event:read", "event:write", "group-member:read", "group-member:write"})
     * @Assert\Uuid
     */
    protected UuidInterface $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"group:read", "group:write"})
     */
    public string $name;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"group:read", "group:write"})
     */
    public bool $discoverable = false;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"group:read", "group:write"})
     */
    public bool $private = true;

    /**
     * @ORM\ManyToOne(targetEntity=Group::class, inversedBy="subGroups")
     * @Groups({"group:read", "group:write"})
     * @ApiFilter(SearchFilter::class, properties={"owner.id": "exact"})
     */
    protected ?Group $owner = null;

    /**
     * @ORM\OneToMany(targetEntity=Group::class, mappedBy="owner")
     * @ApiSubresource(maxDepth=1)
     */
    private $subGroups;

    /**
     * @ORM\OneToMany(targetEntity=GroupMember::class, mappedBy="userGroup", orphanRemoval=true)
     * @ApiSubresource(maxDepth=1)
     */
    private $groupMembers;

    /**
     * @ORM\OneToMany(targetEntity=Event::class, mappedBy="eventGroup", orphanRemoval=true)
     * @ApiSubresource()
     */
    private $events;

    public function __construct()
    {
        $this->subGroups = new ArrayCollection();
        $this->groupMembers = new ArrayCollection();
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
    public function getSubGroups()
    {
        return $this->subGroups->getValues();
    }

    public function addSubGroup(self $subGroup): void
    {
        if (!$this->subGroups->contains($subGroup)) {
            $this->subGroups[] = $subGroup;
            $subGroup->setOwner($this);
        }
    }

    public function removeSubGroup(self $subGroup): void
    {
        if ($this->subGroups->contains($subGroup)) {
            $this->subGroups->removeElement($subGroup);
            // set the owning side to null (unless already changed)
            if ($subGroup->getOwner() === $this) {
                $subGroup->setOwner(null);
            }
        }
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
            $groupMember->setUserGroup($this);
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
            $event->setEventGroup($this);
        }
    }

    public function removeEvent(Event $event): void
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
        }
    }
}
