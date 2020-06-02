<?php
namespace Productively\Api;

use ApiPlatform\Core\Bridge\Symfony\Messenger\RemoveStamp;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use Productively\Api\Entity\Event;
use Productively\Api\Entity\GroupMember;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

final class DataPersister implements ContextAwareDataPersisterInterface
{
    private DataPersisterInterface $decorated;
    private Security $security;
    private MessageBusInterface $messageBus;

    public function __construct(DataPersisterInterface $decorated, Security $security, MessageBusInterface $messageBus)
    {
        $this->decorated = $decorated;
        $this->security = $security;
        $this->messageBus = $messageBus;
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

        $isEphemeralEvent = $data instanceof Event && $data->isEphemeral();
        if(!$isEphemeralEvent) {
            $result = $this->decorated->persist($data, $context);
        } else {
            $result = $data;
        }

        $this->messageBus->dispatch($data);

        return $result;
    }

    public function remove($data, array $context = [])
    {
        $result = $this->decorated->remove($data, $context);

        $this->messageBus->dispatch($data, [new RemoveStamp()]);

        return $result;
    }
}