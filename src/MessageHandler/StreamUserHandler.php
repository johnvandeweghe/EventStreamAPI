<?php
namespace EventStreamApi\MessageHandler;

use Doctrine\Persistence\ObjectManager;
use EventStreamApi\Entity\Event;
use EventStreamApi\Entity\StreamUser;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Doctrine\Persistence\ManagerRegistry;
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

    public function __invoke(StreamUser $streamUser): void
    {
        $manager = $this->managerRegistry->getManagerForClass(get_class($streamUser));
        if(!$manager){
            return;
        }

        $deleted = !in_array($streamUser, $streamUser->getUser()->getStreamUsers());

        if(!$deleted) {
            //Add default user role.
            $streamUser->addRole($streamUser->getStream()->getDefaultUserRole());
            //Todo: Add roles user has at parent? Propagate admin rights basically.
        }

        $this->markUserJoiningOrLeaving($streamUser, $deleted, $manager);

        //Alert the user that they were added to a child so they can live update - ephemeral
        if(!$deleted && $streamUser->getUser() !== $this->security->getUser() && $streamUser->getStream()->getOwner()) {
            $addedEvent = Event::createEphemeralMarkerEvent(Event::MARK_USER_ADDED_TO_CHILD);
            $addedEvent->setStream($streamUser->getStream()->getOwner());
            $addedEvent->setUser($streamUser->getUser());

            $this->messageBus->dispatch($addedEvent);
        }
    }

    /**
     * Log the user joining or leaving the channel (for UI and access logging)
     * @param StreamUser $streamUser
     * @param bool $userLeft
     * @param ObjectManager $manager
     */
    protected function markUserJoiningOrLeaving(StreamUser $streamUser, bool $userLeft, ObjectManager $manager): void
    {
        $event = new Event();
        $event->setUser($streamUser->getUser());
        $event->setStream($streamUser->getStream());
        $event->datetime = new \DateTimeImmutable();
        $event->type = $userLeft ? Event::MARK_USER_LEFT : Event::MARK_USER_JOINED;
        $event->ephemeral = false;

        $manager->persist($event);
        $manager->flush();
    }
}