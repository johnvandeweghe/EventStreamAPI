<?php

namespace Productively\Api\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="Productively\Api\Repository\GroupRepository")
 * @ORM\Table(name="`group`")
 */
class Group
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

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

    public function __construct()
    {
        $this->subGroups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getOwner(): ?self
    {
        return $this->owner;
    }

    public function setOwner(?self $owner): self
    {
        $this->owner = $owner;

        return $this;
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
}
