<?php

namespace PostChat\Api\Entity\EventData;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;
use PostChat\Api\Repository\EventData\CommandEventDataRepository;

/**
 * @ApiResource(
 *     collectionOperations={},
 *     itemOperations={"get"}
 * )
 * @ORM\Entity(repositoryClass=CommandEventDataRepository::class)
 */
class CommandEventData
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
     * @ORM\Column(type="string")
     * @Groups({"event:read", "event:write"})
     */
    public string $command;

    /**
     * @ORM\Column(type="json")
     * @Groups({"event:read", "event:write"})
     */
    public array $parameters;

    public function getId(): UuidInterface
    {
        return $this->id;
    }
}
