<?php
namespace PostChat\Api\MessageHandler;

use PostChat\Api\Entity\Event;
use PostChat\Api\Entity\StreamUser;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

class StreamUserHandler implements MessageHandlerInterface
{
    private ManagerRegistry $managerRegistry;
    private Security $security;
    private MessageBusInterface $messageBus;

    public function __construct(ManagerRegistry $managerRegistry, Security $security, MessageBusInterface $messageBus)
    {
        $this->managerRegistry = $managerRegistry;
        $this->security = $security;
        $this->messageBus = $messageBus;
    }

    public function __invoke(StreamUser $streamUser)
    {
        $manager = $this->managerRegistry->getManagerForClass(get_class($streamUser));
        if(!$manager){
            return;
        }

        $deleted = !in_array($streamUser, $streamUser->getUser()->getStreamUsers());

        //Log the user joining or leaving the channel (for UI and access logging)
        $event = new Event();
        $event->setUser($streamUser->getUser());
        $event->setStream($streamUser->getStream());
        $event->datetime = new \DateTimeImmutable();
        $event->type = $deleted ? Event::TYPE_USER_LEFT : Event::TYPE_USER_JOINED;

        //Alert the user that they were added to a child so they can live update - ephemeral
        if(!$deleted && $streamUser->getUser() !== $this->security->getUser() && $streamUser->getStream()->getOwner()) {
            $addedEvent = Event::createEphemeralEvent();
            $addedEvent->setStream($streamUser->getStream()->getOwner());
            $addedEvent->setUser($streamUser->getUser());
            $addedEvent->type = Event::TYPE_USER_ADDED_TO_CHILD;

            $this->messageBus->dispatch($addedEvent);
        }

        $manager->persist($event);
        $manager->flush();

        $manager->refresh($event);
        $this->messageBus->dispatch($event);
    }
}