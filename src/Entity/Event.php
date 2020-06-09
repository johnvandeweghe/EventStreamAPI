<?php

namespace Productively\Api\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={"get","post"},
 *     itemOperations={"get"},
 *     normalizationContext={"groups"={"event:read"}},
 *     denormalizationContext={"groups"={"event:write"}},
 *     attributes={"order"={"datetime": "ASC"},"validation_groups"={Event::class, "validationGroups"}}
 * )
 * @ORM\Entity(repositoryClass="Productively\Api\Repository\EventRepository")
 * @ORM\Table(indexes={@ORM\Index(name="group_datetime", columns={"event_group_id", "datetime"})})
 */
class Event
{
    public const TYPE_MESSAGE       = "message";
    public const TYPE_TYPING_START  = "typing-start";
    public const TYPE_TYPING_STOP   = "typing-stop";
    public const TYPE_GROUP_JOINED  = "joined";
    public const TYPE_GROUP_LEFT    = "left";
    public const EPHEMERAL_TYPES = [self::TYPE_TYPING_START, self::TYPE_TYPING_STOP];
    public const TYPES = [
        self::TYPE_MESSAGE,
        self::TYPE_TYPING_START,
        self::TYPE_TYPING_STOP,
        self::TYPE_GROUP_JOINED,
        self::TYPE_GROUP_LEFT
    ];
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Groups({"event:read", "group-member:read"})
     * @Assert\Uuid(groups={"Default", "message_event"})
     */
    protected UuidInterface $id;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"event:read"})
     */
    public \DateTimeImmutable $datetime;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"event:read", "event:write"})
     * @Assert\Choice(choices=Event::TYPES)
     * @Assert\NotBlank(groups={"Default", "message_event"})
     * @ApiProperty(
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *             "enum"=Event::TYPES,
     *             "example"=EVENT::TYPE_MESSAGE
     *         }
     *     }
     * )
     */
    public string $type;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"event:read"})
     */
    protected User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Group::class, inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"event:read", "event:write"})
     * @ApiFilter(SearchFilter::class, properties={"eventGroup.id": "exact"})
     */
    protected Group $eventGroup;

    /**
     * @ORM\OneToOne(targetEntity=MessageEventData::class, cascade={"persist", "remove"})
     * @Groups({"event:read", "event:write"})
     * @Assert\NotBlank(groups={"message_event"})
     * @Assert\IsNull()
     */
    protected ?MessageEventData $messageEventData = null;

    /**
     * @param Event $event
     * @return string[]
     */
    public static function validationGroups(self $event): array
    {
        if($event->type === self::TYPE_MESSAGE) {
            return ["message_event"];
        }
        return ["Default"];
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * For setting the id when the event is ephemeral. Unused otherwise.
     * @param UuidInterface $uuid
     */
    public function setId(UuidInterface $uuid): void
    {
        $this->id = $uuid;
    }

    public function getEventGroup(): Group
    {
        return $this->eventGroup;
    }

    public function setEventGroup(Group $eventGroup): void
    {
        $this->eventGroup = $eventGroup;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
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

    public function isEphemeral(): bool
    {
        return in_array($this->type, self::EPHEMERAL_TYPES);
    }
}
