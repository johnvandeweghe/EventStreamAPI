<?php

namespace Productively\Api\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Ramsey\Uuid\UuidInterface;

/**
 * @ApiResource(
 *     collectionOperations={},
 *     itemOperations={"get"}
 * )
 * @ORM\Entity(repositoryClass="Productively\Api\Repository\MessageEventDataRepository")
 */
class MessageEventData
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
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
