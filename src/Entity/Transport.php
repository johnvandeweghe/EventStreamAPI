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
     * @Groups({"transport:read"})
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public ?string $returnSecret;

    public function getName(): ?string
    {
        return $this->name;
    }
}
