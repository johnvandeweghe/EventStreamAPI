<?php

namespace PostChat\Api\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={},
 *     itemOperations={"get"}
 * )
 * @ORM\Entity(repositoryClass="PostChat\Api\Repository\WebhookSubscriptionDataRepository")
 */
class WebhookSubscriptionData
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
     * @Groups({"subscription:read", "subscription:write"})
     * @Assert\Url()
     */
    public string $uri;

    public function getId(): UuidInterface
    {
        return $this->id;
    }
}
