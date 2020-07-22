<?php

namespace Productively\Api\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
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
 * @ORM\Table(indexes={
 *     @ORM\Index(name="idx_group_datetime", columns={"event_group_id", "datetime"}),
 *     @ORM\Index(name="idx_group_user_datetime", columns={"event_group_id", "user_id, "datetime"})
 * })
 */
class Event
{
    public const TYPE_MESSAGE               = "message";
    public const TYPE_COMMAND               = "command";
    public const TYPE_TYPING_START          = "typing-start";
    public const TYPE_TYPING_STOP           = "typing-stop";
    public const TYPE_CHILD_GROUP_CREATED   = "group-created";
    public const TYPE_USER_JOINED           = "user-joined";
    public const TYPE_USER_LEFT             = "user-left";
    public const TYPE_USER_ADDED_TO_CHILD   = "user-added-to-child";

    public const EPHEMERAL_TYPES = [
        self::TYPE_TYPING_START,
        self::TYPE_TYPING_STOP,
        self::TYPE_CHILD_GROUP_CREATED,
        self::TYPE_USER_ADDED_TO_CHILD
    ];

    public const TYPES = [
        self::TYPE_MESSAGE,
        self::TYPE_COMMAND,
        self::TYPE_TYPING_START,
        self::TYPE_TYPING_STOP,
        self::TYPE_CHILD_GROUP_CREATED,
        self::TYPE_USER_JOINED,
        self::TYPE_USER_LEFT,
        self::TYPE_USER_ADDED_TO_CHILD
    ];

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Groups({"event:read", "group-member:read", "group-member:write"})
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
     * @Assert\IsNull(groups={"Default", "command_event"})
     */
    protected ?MessageEventData $messageEventData = null;

    /**
     * @ORM\OneToOne(targetEntity=CommandEventData::class, cascade={"persist", "remove"})
     * @Groups({"event:read", "event:write"})
     * @Assert\NotBlank(groups={"command_event"})
     * @Assert\IsNull(groups={"Default", "message_event"})
     */
    protected ?CommandEventData $commandEventData = null;

    /**
     * @param Event $event
     * @return string[]
     */
    public static function validationGroups(self $event): array
    {
        if ($event->type === self::TYPE_MESSAGE) {
            return ["message_event"];
        }

        if($event->type === self::TYPE_COMMAND) {
            return ["command_event"];
        }

        return ["Default"];
    }

    /**
     * Creates an event that has private fields the ORM usually sets set to reasonable values for dispatch.
     * Convenience function.
     * @return static
     */
    public static function createEphemeralEvent(): self
    {
        $event = new self();
        $event->setId(Uuid::uuid4());
        $event->datetime = new \DateTimeImmutable();

        return $event;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * For setting/faking the id when the event is ephemeral. Unused otherwise.
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

    public function setMessageEventData(?MessageEventData $messageEventData): void
    {
        $this->messageEventData = $messageEventData;
    }

    public function getCommandEventData(): ?CommandEventData
    {
        return $this->commandEventData;
    }

    public function setCommandEventData(?CommandEventData $commandEventData): void
    {
        $this->commandEventData = $commandEventData;
    }

    public function isEphemeral(): bool
    {
        return in_array($this->type, self::EPHEMERAL_TYPES);
    }
}
