<?php

namespace Productively\Api\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="Productively\Api\Repository\EventRepository")
 */
class Event
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    protected UuidInterface $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public string $userIdentifier;

    /**
     * @ORM\Column(type="datetime")
     */
    public \DateTimeImmutable $datetime;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public string $type;

    /**
     * @ORM\ManyToOne(targetEntity="Productively\Api\Entity\Group", inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $eventGroup;

    /**
     * @ORM\OneToOne(targetEntity="Productively\Api\Entity\MessageEventData", cascade={"persist", "remove"})
     */
    protected $messageEventData;

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getEventGroup(): Group
    {
        return $this->eventGroup;
    }

    public function setEventGroup(Group $eventGroup): self
    {
        $this->eventGroup = $eventGroup;

        return $this;
    }

    public function getMessageEventData(): ?MessageEventData
    {
        return $this->messageEventData;
    }

    public function setMessageEventData(?MessageEventData $messageEventData): self
    {
        $this->messageEventData = $messageEventData;

        return $this;
    }
}
