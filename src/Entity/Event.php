<?php

namespace EventStreamApi\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use EventStreamApi\Repository\EventRepository;
use Ramsey\Uuid\Nonstandard\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Ramsey\Uuid\UuidInterface;

/**
 * TODO={"security"="user.getSreamUserForStream(object.stream).hasPermission('stream:write')"}
 * @ApiResource(
 *     collectionOperations={"get","post"},
 *     itemOperations={"get"},
 *     normalizationContext={
 *         "groups"={"event:read"}
 *     },
 *     denormalizationContext={"groups"={"event:write"}},
 *     attributes={"order"={"datetime": "DESC"}}
 * )
 * @ORM\Entity(repositoryClass=EventRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="idx_e_stream_datetime", columns={"stream_id", "datetime"}),
 *     @ORM\Index(name="idx_e_stream_user_datetime", columns={"stream_id", "user_id", "datetime"}),
 *     @ORM\Index(name="idx_e_stream_transport_datetime", columns={"stream_id", "transport_id", "datetime"}),
 *     @ORM\Index(name="idx_e_stream_type_datetime", columns={"stream_id", "type", "datetime"})
 * })
 */
class Event
{
    public const MARK_CHILD_STREAM_CREATED  = "stream-created";
    public const MARK_USER_ADDED_TO_CHILD   = "user-added-to-child";
    public const MARK_USER_JOINED           = "user-joined";
    public const MARK_USER_LEFT             = "user-left";

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
     * A short string to represent what this event means.
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"event:read", "event:write"})
     */
    public string $type;

    /**
     * Whether or not this event should be persisted.
     * @Groups({"event:read", "event:write"})
     */
    public bool $ephemeral = false;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"event:read"})
     */
    protected ?User $user = null; // This will be set to the current user if created from the api

    /**
     * @ORM\ManyToOne(targetEntity=Stream::class, inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"event:read", "event:write"})
     * @ApiFilter(SearchFilter::class, properties={"stream.id": "exact"})
     */
    protected Stream $stream;

    /**
     * @ORM\OneToOne(targetEntity=EventData::class, cascade={"persist", "remove"})
     * @Groups({"event:read", "event:write"})
     */
    protected ?EventData $eventData = null;


    /**
     * The transport that created this event, if any.
     * @ORM\ManyToOne(targetEntity=Transport::class)
     * @ORM\JoinColumn(name="transport_id", referencedColumnName="name")
     * @Groups({"event:read"})
     */
    protected ?Transport $transport = null;

    /**
     * Creates an event that has private fields the ORM usually sets set to reasonable values for dispatch.
     * Convenience function.
     * @param string $mark
     * @return self
     */
    public static function createEphemeralMarkerEvent(string $mark): self
    {
        $event = new self();
        $event->setId(Uuid::uuid4());
        $event->datetime = new \DateTimeImmutable();
        $event->type = $mark;
        $event->ephemeral = true;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getEventData(): ?EventData
    {
        return $this->eventData;
    }

    public function setEventData(?EventData $eventData): void
    {
        $this->eventData = $eventData;
    }

    public function getTransport(): ?Transport
    {
        return $this->transport;
    }

    public function setTransport(?Transport $transport): void
    {
        $this->transport = $transport;
    }
}
