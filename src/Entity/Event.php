<?php

namespace EventStreamApi\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use EventStreamApi\Entity\EventData\CommandEventData;
use EventStreamApi\Entity\EventData\MarkerEventData;
use EventStreamApi\Entity\EventData\MessageEventData;
use EventStreamApi\Repository\EventRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TODO={"security"="user.getSreamUserForStream(object.stream).hasPermission('stream:write')"}
 * @ApiResource(
 *     collectionOperations={"get","post"},
 *     itemOperations={"get"},
 *     normalizationContext={
 *         "groups"={"event:read"}
 *     },
 *     denormalizationContext={"groups"={"event:write"}},
 *     attributes={"order"={"datetime": "DESC"},"validation_groups"={Event::class, "validationGroups"}}
 * )
 * @ORM\Entity(repositoryClass=EventRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="idx_e_stream_datetime", columns={"stream_id", "datetime"}),
 *     @ORM\Index(name="idx_e_stream_user_datetime", columns={"stream_id", "user_id", "datetime"})
 * })
 */
class Event
{
    public const TYPE_MESSAGE               = "message";
    public const TYPE_MARKER                = "marker";


    public const TYPES = [
        self::TYPE_MESSAGE,
        self::TYPE_MARKER
    ];

    public const VALIDATION_DEFAULT = "Default";

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Groups({"event:read", "stream-user:read", "stream-user:write"})
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
     * @Assert\NotBlank(groups={
     *     Event::VALIDATION_DEFAULT,
     *     Event::TYPE_MESSAGE,
     *     Event::TYPE_MARKER
     * })
     * @ApiProperty(
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *             "enum"=Event::TYPES,
     *             "example"=Event::TYPE_MESSAGE
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
     * @ORM\ManyToOne(targetEntity=Stream::class, inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"event:read", "event:write"})
     * @ApiFilter(SearchFilter::class, properties={"stream.id": "exact"})
     */
    protected Stream $stream;

    /**
     * @ORM\OneToOne(targetEntity=MessageEventData::class, cascade={"persist", "remove"})
     * @Groups({"event:read", "event:write"})
     * @Assert\NotBlank(groups={Event::TYPE_MESSAGE})
     * @Assert\IsNull(groups={Event::VALIDATION_DEFAULT, Event::TYPE_MARKER})
     */
    protected ?MessageEventData $messageEventData = null;

    /**
     * @ORM\OneToOne(targetEntity=MarkerEventData::class, cascade={"persist", "remove"})
     * @Groups({"event:read", "event:write"})
     * @Assert\NotBlank(groups={Event::TYPE_MARKER})
     * @Assert\IsNull(groups={Event::VALIDATION_DEFAULT, Event::TYPE_MESSAGE})
     */
    protected ?MarkerEventData $markerEventData = null;

    /**
     * @param Event $event
     * @return string[]
     */
    public static function validationGroups(self $event): array
    {
        if ($event->type === self::TYPE_MESSAGE) {
            return [self::TYPE_MESSAGE];
        }

        if($event->type === self::TYPE_MARKER) {
            return [self::TYPE_MARKER];
        }

        return [self::VALIDATION_DEFAULT];
    }

    /**
     * Creates an event that has private fields the ORM usually sets set to reasonable values for dispatch.
     * Convenience function.
     * @param string $mark
     * @return static
     */
    public static function createEphemeralMarkerEvent(string $mark): self
    {
        $event = new self();
        $event->setId(Uuid::uuid4());
        $event->datetime = new \DateTimeImmutable();
        $event->type = self::TYPE_MARKER;
        $event->markerEventData = new MarkerEventData($mark, true);

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

    public function getStream(): Stream
    {
        return $this->stream;
    }

    public function setStream(Stream $stream): void
    {
        $this->stream = $stream;
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

    public function getMarkerData(): ?MarkerEventData
    {
        return $this->markerEventData;
    }

    public function setMarkerData(?MarkerEventData $markerEventData): void
    {
        $this->markerEventData = $markerEventData;
    }
}
