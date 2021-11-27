<?php

namespace EventStreamApi\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use EventStreamApi\Repository\TransportRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 *     normalizationContext={
 *         "groups"={"transport:read"}
 *     }
 * )
 * @ORM\Entity(repositoryClass=TransportRepository::class)
 */
class Transport
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     * @Groups({"transport:read", "event:read"})
     */
    private string $name;

    /**
     * Pem formatted public key that corresponds to the transport's private key it will sign return messages with.
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public ?string $publicKey;

    public function __construct(string $name, ?string $publicKey)
    {
        $this->name = $name;
        $this->publicKey = $publicKey;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
