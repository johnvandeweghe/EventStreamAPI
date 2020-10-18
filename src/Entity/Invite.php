<?php

namespace PostChat\Api\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use PostChat\Api\Repository\InviteRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get"={"security"="false"},
 *         "post"={
 *             "security_post_denormalize"="object.getStream().private && user.getStreamUserForStream(object.getStream()).hasPermission('stream:invite')"
 *         }
 *     },
 *     itemOperations={"get"},
 *     normalizationContext={"groups"={"invite:read"}},
 *     denormalizationContext={"groups"={"invite:write"}}
 * )
 * @ORM\Entity(repositoryClass=InviteRepository::class)
 */
class Invite
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Groups({"invite:read", "stream-user:create"})
     */
    protected UuidInterface $id;

    /**
     * @ORM\ManyToOne(targetEntity=Stream::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"invite:read", "invite:write"})
     */
    protected Stream $stream;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"invite:read", "invite:write"})
     */
    public \DateTimeImmutable $expiration;

    /**
     * @ORM\OneToOne(targetEntity=StreamUser::class, mappedBy="invite", cascade={"persist", "remove"})
     */
    protected ?StreamUser $invitedStreamUser;

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

    public function getInvitedStreamUser(): ?StreamUser
    {
        return $this->invitedStreamUser;
    }

    public function setInvitedStreamUser(?StreamUser $invitedStreamUser): void
    {
        $this->invitedStreamUser = $invitedStreamUser;

        // set (or unset) the owning side of the relation if necessary
        $newInvite = null === $invitedStreamUser ? null : $this;
        if ($invitedStreamUser->getInvite() !== $newInvite) {
            $invitedStreamUser->setInvite($newInvite);
        }
    }
}
