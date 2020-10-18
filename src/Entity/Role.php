<?php

namespace PostChat\Api\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use PostChat\Api\Repository\RoleRepository;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get"
 *     },
 *     itemOperations={
 *         "get"
 *     },
 *     normalizationContext={
 *         "groups"={"role:read"}
 *     },
 *     denormalizationContext={
 *         "groups"={"role:write"}
 *     }
 * )
 * @ORM\Entity(repositoryClass=RoleRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="idx_r_stream_id", columns={"stream_id"})
 * })
 */
class Role
{
    public const STREAM_ARCHIVE = "stream:archive";
    public const STREAM_CREATE = "stream:create";
    public const STREAM_ROLES = "stream:roles";
    public const STREAM_EDIT = "stream:edit";
    public const STREAM_ACCESS = "stream:access";
    public const STREAM_INVITE = "stream:invite";
    public const STREAM_JOIN = "stream:join";
    public const STREAM_KICK = "stream:kick";
    public const STREAM_WRITE = "stream:write";
    public const STREAM_READ = "stream:read";

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Groups({"stream:read", "stream:create", "stream:update", "role:read", "stream-user:read"})
     * @Assert\Uuid
     */
    protected UuidInterface $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"role:read", "role:update", "role:create", "stream-user:read"})
     */
    public string $name;

    /**
     * Allows archiving (soft deleting) of stream.
     * @ORM\Column(type="boolean")
     * TODO: enforce
     */
    public bool $streamArchive = false;

    /**
     * Allows creating child streams.
     * @ORM\Column(type="boolean")
     * TODO: enforce
     */
    public bool $streamCreate = false;

    /**
     * Allows editing and assigning roles.
     * @ORM\Column(type="boolean")
     * TODO: enforce
     */
    public bool $streamRoles = false;

    /**
     * Allows editing the meta fields on a stream (name, etc).
     * @ORM\Column(type="boolean")
     * TODO: enforce
     */
    public bool $streamEdit = false;

    /**
     * Allows editing the private and discoverable fields on a stream.
     * @ORM\Column(type="boolean")
     * TODO: enforce
     */
    public bool $streamAccess = false;

    /**
     * Allows inviting users to the stream.
     * @ORM\Column(type="boolean")
     * @Groups({"role:read", "role:update", "role:create"})
     */
    public bool $streamInvite = false;

    /**
     * Allows to join child streams.
     * @ORM\Column(type="boolean")
     * TODO: enforce
     */
    public bool $streamJoin = false;

    /**
     * Allows to kick users from the stream.
     * @ORM\Column(type="boolean")
     * TODO: enforce
     */
    public bool $streamKick = false;

    /**
     * Allows to write events to the stream.
     * @ORM\Column(type="boolean")
     * TODO: enforce
     */
    public bool $streamWrite = false;

    /**
     * Allows to read events from the stream.
     * @ORM\Column(type="boolean")
     * TODO: enforce
     */
    public bool $streamRead = false;

    /**
     * @ORM\ManyToOne(targetEntity=Stream::class, inversedBy="roles")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"role:read", "role:create"})
     * @ApiFilter(SearchFilter::class, properties={"stream.id": "exact"})
     */
    private Stream $stream;

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getStream(): Stream
    {
        return $this->stream;
    }

    public function setStream(Stream $stream): void
    {
        $this->stream = $stream;
    }

    public function hasPermission(string $permission): bool
    {
        return $permission === self::STREAM_INVITE && $this->streamInvite;
    }
}
