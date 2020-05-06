<?php

namespace Productively\Api\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ApiResource()
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
     */
    protected UuidInterface $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public string $name;

    /**
     * @ORM\Column(type="boolean")
     */
    public bool $discoverable;

    /**
     * @ORM\Column(type="boolean")
     */
    public bool $private;

    /**
     * @ORM\ManyToOne(targetEntity="Productively\Api\Entity\Group", inversedBy="subGroups")
     */
    private $owner;

    /**
     * @ORM\OneToMany(targetEntity="Productively\Api\Entity\Group", mappedBy="owner")
     */
    private $subGroups;

    /**
     * @ORM\OneToMany(targetEntity="Productively\Api\Entity\GroupMember", mappedBy="userGroup", orphanRemoval=true)
     */
    private $groupMembers;

    public function __construct()
    {
        $this->subGroups = new ArrayCollection();
        $this->groupMembers = new ArrayCollection();
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
     * @return Collection|self[]
     */
    public function getSubGroups(): Collection
    {
        return $this->subGroups;
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
     * @return Collection|GroupMember[]
     */
    public function getGroupMembers(): Collection
    {
        return $this->groupMembers;
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
}
