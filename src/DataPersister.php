<?php
namespace Productively\Api;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use Productively\Api\Entity\Event;
use Productively\Api\Entity\GroupMember;
use Symfony\Component\Security\Core\Security;

final class DataPersister implements ContextAwareDataPersisterInterface
{
    private DataPersisterInterface $decorated;
    private Security $security;

    public function __construct(DataPersisterInterface $decorated, Security $security)
    {
        $this->decorated = $decorated;
        $this->security = $security;
    }

    public function supports($data, array $context = []): bool
    {
        return $this->decorated->supports($data, $context);
    }

    public function persist($data, array $context = [])
    {
        if ($data instanceof Event && ($user = $this->security->getUser())) {
            $data->userIdentifier = $user->getUsername();
            $data->datetime = new \DateTimeImmutable();
        }
        if ($data instanceof GroupMember && ($user = $this->security->getUser())) {
            $data->userIdentifier = $user->getUsername();
        }

        return $this->decorated->persist($data, $context);
    }

    public function remove($data, array $context = [])
    {
        return $this->decorated->remove($data, $context);
    }
}