<?php
namespace PostChat\Api\MessageHandler;

use PostChat\Api\Entity\Event;
use PostChat\Api\Entity\Stream;
use PostChat\Api\Entity\StreamUser;
use PostChat\Api\Entity\User;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Persistence\ManagerRegistry;

class StreamHandler implements MessageHandlerInterface
{
    private Security $security;
    private ManagerRegistry $managerRegistry;
    private MessageBusInterface $messageBus;

    public function __construct(Security $security, ManagerRegistry $managerRegistry, MessageBusInterface $messageBus)
    {
        $this->security = $security;
        $this->managerRegistry = $managerRegistry;
        $this->messageBus = $messageBus;
    }

    public function __invoke(Stream $stream)
    {
        $manager = $this->managerRegistry->getManagerForClass(get_class($stream));
        /**
         * @var $user User
         */
        if(!$manager || !($user = $this->security->getUser()) || $stream->hasUser($user)){
            return;
        }

        $streamUser = new StreamUser();
        $streamUser->setUser($user);
        $stream->addStreamUser($streamUser);

        $manager->persist($streamUser);
        $manager->persist($stream);

        //Alert the parent stream that there is a new child to live update stream lists
        if($stream->discoverable && ($owner = $stream->getOwner())) {
            $streamAddedEvent = Event::createEphemeralEvent();
            $streamAddedEvent->setStream($owner);
            $streamAddedEvent->setUser($user);
            $streamAddedEvent->type = Event::TYPE_CHILD_STREAM_CREATED;

            $this->messageBus->dispatch($streamAddedEvent);
        }

        $manager->flush();
        $manager->refresh($streamUser);

        $this->messageBus->dispatch($streamUser);
    }
}