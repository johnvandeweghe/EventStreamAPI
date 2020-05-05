<?php

namespace Productively\Api\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="Productively\Api\Repository\GroupMemberRepository")
 */
class GroupMember
{
    /**
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    protected UuidInterface $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $userIdentifer;

    /**
     * @ORM\ManyToOne(targetEntity="Productively\Api\Entity\Group", inversedBy="groupMembers")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $userGroup;

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
}
