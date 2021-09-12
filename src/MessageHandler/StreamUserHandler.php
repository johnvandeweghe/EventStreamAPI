<?php
namespace EventStreamApi\MessageHandler;

use EventStreamApi\DataPersister;
use EventStreamApi\Entity\Event;
use EventStreamApi\Entity\StreamUser;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\Security;

class StreamUserHandler implements MessageHandlerInterface
{
    public function __construct(
        private Security $security,
        private DataPersister $dataPersister
    ) {}

    public function __invoke(StreamUser $streamUser): void
    {
        $deleted = !in_array($streamUser, $streamUser->getUser()->getStreamUsers());

        if(!$deleted) {
            //Add default user role.
            $streamUser->addRole($streamUser->getStream()->getDefaultUserRole());
            //Todo: Add roles user has at parent? Propagate admin rights basically.
        }

        $this->markUserJoiningOrLeaving($streamUser, $deleted);

        //Alert the user that they were added to a child so they can live update - ephemeral
        if(!$deleted && $streamUser->getUser() !== $this->security->getUser() && $streamUser->getStream()->getOwner()) {
            $this->markUserAddedToChild($streamUser);
        }
    }

    /**
     * Log the user joining or leaving the channel (for UI and access logging)
     * @param StreamUser $streamUser
     * @param bool $userLeft
     */
    protected function markUserJoiningOrLeaving(StreamUser $streamUser, bool $userLeft): void
    {
        $event = new Event();
        $event->setUser($streamUser->getUser());
        $event->setStream($streamUser->getStream());
        $event->type = $userLeft ? Event::MARK_USER_LEFT : Event::MARK_USER_JOINED;

        $this->dataPersister->persist($event);
    }

    /**
     * @param StreamUser $streamUser
     */
    private function markUserAddedToChild(StreamUser $streamUser): void
    {
        $addedEvent = new Event();
        $addedEvent->type = Event::MARK_USER_ADDED_TO_CHILD;
        $addedEvent->ephemeral = true;
        $addedEvent->setStream($streamUser->getStream()->getOwner());
        $addedEvent->setUser($streamUser->getUser());

        $this->dataPersister->persist($addedEvent);
    }
}