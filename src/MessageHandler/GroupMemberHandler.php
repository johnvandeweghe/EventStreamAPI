<?php
namespace Productively\Api\MessageHandler;

use Productively\Api\Entity\Event;
use Productively\Api\Entity\GroupMember;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

class GroupMemberHandler implements MessageHandlerInterface
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

    public function __invoke(GroupMember $member)
    {
        $manager = $this->managerRegistry->getManagerForClass(get_class($member));
        if(!$manager){
            return;
        }

        $deleted = !in_array($member, $member->getUser()->getGroupMembers());

        //Log the user joining or leaving the channel (for UI and access logging)
        $event = new Event();
        $event->setUser($member->getUser());
        $event->setEventGroup($member->getUserGroup());
        $event->datetime = new \DateTimeImmutable();
        $event->type = $deleted ? Event::TYPE_USER_LEFT : Event::TYPE_USER_JOINED;

        //Alert the user that they were added to a child so they can live update - ephemeral
        if(!$deleted && $member->getUser() !== $this->security->getUser() && $member->getUserGroup()->getOwner()) {
            $addedEvent = Event::createEphemeralEvent();
            $addedEvent->setEventGroup($member->getUserGroup()->getOwner());
            $addedEvent->setUser($member->getUser());
            $addedEvent->type = Event::TYPE_USER_ADDED_TO_CHILD;

            $this->messageBus->dispatch($addedEvent);
        }

        $manager->persist($event);
        $manager->flush();

        $manager->refresh($event);
        $this->messageBus->dispatch($event);
    }
}