<?php

namespace EventStreamApi\Entity\EventData;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;
use EventStreamApi\Repository\EventData\MarkerEventDataRepository;

/**
 * @ApiResource(
 *     collectionOperations={},
 *     itemOperations={"get"}
 * )
 * @ORM\Entity(repositoryClass=MarkerEventDataRepository::class)
 */
class MarkerEventData
{
    //Programmatically generated marks.
    public const MARK_CHILD_STREAM_CREATED  = "stream-created";
    public const MARK_USER_ADDED_TO_CHILD   = "user-added-to-child";
    public const MARK_USER_JOINED           = "user-joined";
    public const MARK_USER_LEFT             = "user-left";

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Assert\Uuid
     */
    protected UuidInterface $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"event:read", "event:write"})
     */
    public string $mark;

    /**
     * @Groups({"event:read", "event:write"})
     */
    public bool $ephemeral;

    public function __construct(string $mark, bool $ephemeral)
    {
        $this->mark = $mark;
        $this->ephemeral = $ephemeral;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }
}
