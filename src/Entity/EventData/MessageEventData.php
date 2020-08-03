<?php

namespace PostChat\Api\Entity\EventData;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;
use PostChat\Api\Repository\EventData\MessageEventDataRepository;

/**
 * @ApiResource(
 *     collectionOperations={},
 *     itemOperations={"get"}
 * )
 * @ORM\Entity(repositoryClass=MessageEventDataRepository::class)
 */
class MessageEventData
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Assert\Uuid
     */
    protected UuidInterface $id;

    /**
     * @ORM\Column(type="text")
     * @Groups({"event:read", "event:write"})
     */
    public string $text;

    public function getId(): UuidInterface
    {
        return $this->id;
    }
}
