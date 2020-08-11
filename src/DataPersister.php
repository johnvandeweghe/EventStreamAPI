<?php
namespace PostChat\Api;

use ApiPlatform\Core\Bridge\Symfony\Messenger\RemoveStamp;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use PostChat\Api\Entity\Event;
use PostChat\Api\Entity\StreamUser;
use PostChat\Api\Entity\User;
use Ramsey\Uuid\Uuid;
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
        /**
         * @var $user User
         */
        $user = $this->security->getUser();
        if(!$user) {
            return $data;
        }

        if ($data instanceof Event) {
            $data->setUser($user);
            $data->datetime = new \DateTimeImmutable();
        }

        $isEphemeralEvent = $data instanceof Event && $data->isEphemeral();
        if(!$isEphemeralEvent) {
            $result = $this->decorated->persist($data, $context);
        } else {
            $result = $data;
            $result->setId(Uuid::uuid4());
        }

        $this->messageBus->dispatch($result);

        return $result;
    }

    public function remove($data, array $context = [])
    {
        $result = $this->decorated->remove($data, $context);

        $this->messageBus->dispatch($data, [new RemoveStamp()]);

        return $result;
    }
}