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

/**
 * @ApiResource(
 *     collectionOperations={"get","post"},
 *     itemOperations={"get"},
 *     normalizationContext={"groups"={"group:read"}},
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
     * @Groups({"group:read", "event:read", "event:write", "group-member:read", "group-member:write"})
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
     * @ORM\ManyToOne(targetEntity="Productively\Api\Entity\Group", inversedBy="subGroups")
     * @Groups({"group:read", "group:write"})
     * @ApiFilter(SearchFilter::class, properties={"owner.id": "exact"})
     */
    private ?Group $owner = null;

    /**
     * @ORM\OneToMany(targetEntity="Productively\Api\Entity\Group", mappedBy="owner")
     * @ApiSubresource(maxDepth=1)
     */
    private $subGroups;

    /**
     * @ORM\OneToMany(targetEntity="Productively\Api\Entity\GroupMember", mappedBy="userGroup", orphanRemoval=true)
     * @ApiSubresource(maxDepth=1)
     */
    private $groupMembers;

    /**
     * @ORM\OneToMany(targetEntity="Productively\Api\Entity\Event", mappedBy="eventGroup", orphanRemoval=true)
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

    public function addSubGroup(self $subGroup): self
    {
        if (!$this->subGroups->contains($subGroup)) {
            $this->subGroups[] = $subGroup;
            $subGroup->setOwner($this);
        }

        return $this;
    }

    public function removeSubGroup(self $subGroup): self
    {
        if ($this->subGroups->contains($subGroup)) {
            $this->subGroups->removeElement($subGroup);
            // set the owning side to null (unless already changed)
            if ($subGroup->getOwner() === $this) {
                $subGroup->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return GroupMember[]
     */
    public function getGroupMembers()
    {
        return $this->groupMembers->getValues();
    }

    public function addGroupMember(GroupMember $groupMember): self
    {
        if (!$this->groupMembers->contains($groupMember)) {
            $this->groupMembers[] = $groupMember;
            $groupMember->setUserGroup($this);
        }

        return $this;
    }

    public function removeGroupMember(GroupMember $groupMember): self
    {
        if ($this->groupMembers->contains($groupMember)) {
            $this->groupMembers->removeElement($groupMember);
            // set the owning side to null (unless already changed)
            if ($groupMember->getUserGroup() === $this) {
                $groupMember->setUserGroup(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events->getValues();
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setEventGroup($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getEventGroup() === $this) {
                $event->setEventGroup(null);
            }
        }

        return $this;
    }
}
