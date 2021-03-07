<?php

namespace EventStreamApi\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use EventStreamApi\Repository\SubscriptionRepository;

/**
 * @ApiResource(
 *     collectionOperations={"get", "post"},
 *     itemOperations={"get", "delete"},
 *     normalizationContext={"groups"={"subscription:read"}},
 *     denormalizationContext={"groups"={"subscription:write"}}
 * )
 * @ORM\Entity(repositoryClass=SubscriptionRepository::class)
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="uq_transport_stream_user", columns={"transport_id", "stream_user_id"})})
 * @UniqueEntity(fields={"transport", "streamUser"})
 */
class Subscription
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Groups({"subscription:read"})
     * @Assert\Uuid
     */
    protected UuidInterface $id;

    /**
     * @ORM\ManyToOne(targetEntity=Transport::class)
     * @ORM\JoinColumn(name="transport_id", referencedColumnName="name")
     * @Groups({"subscription:read", "subscription:write"})
     * @ApiFilter(SearchFilter::class, strategy="exact")
     * @Assert\NotBlank
     */
    public Transport $transport;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     * @Groups({"subscription:read", "subscription:write"})
     * @var string[]|null
     */
    public ?array $eventTypes;

    /**
     * @ORM\ManyToOne(targetEntity=StreamUser::class, inversedBy="subscriptions")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"subscription:read", "subscription:write"})
     */
    protected StreamUser $streamUser;

    /**
     * Configuration data to be passed to the transport. Each transport will expect something different here.
     * @Groups({"subscription:read", "subscription:write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public ?string $transportConfiguration = null;

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getStreamUser(): StreamUser
    {
        return $this->streamUser;
    }

    public function setStreamUser(StreamUser $streamUser): void
    {
        $this->streamUser = $streamUser;
    }

    public function getTransport(): Transport
    {
        return $this->transport;
    }

    public function setTransport(Transport $transport): void
    {
        $this->transport = $transport;
    }
}
