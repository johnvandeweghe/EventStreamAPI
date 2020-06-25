<?php
namespace Productively\Api\MessageHandler;

use Productively\Api\Entity\Event;
use Productively\Api\Entity\Group;
use Productively\Api\Entity\GroupMember;
use Productively\Api\Entity\User;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Persistence\ManagerRegistry;

class GroupHandler implements MessageHandlerInterface
{
    private Security $security;
    /**
     * @var ManagerRegistry
     */
    private ManagerRegistry $managerRegistry;
    /**
     * @var MessageBusInterface
     */
    private MessageBusInterface $messageBus;

    public function __construct(Security $security, ManagerRegistry $managerRegistry, MessageBusInterface $messageBus)
    {
        $this->security = $security;
        $this->managerRegistry = $managerRegistry;
        $this->messageBus = $messageBus;
    }

    public function __invoke(Group $group)
    {
        $manager = $this->managerRegistry->getManagerForClass(get_class($group));
        /**
         * @var $user User
         */
        if(!$manager || !($user = $this->security->getUser()) || $group->hasUser($user)){
            return;
        }

        $groupMember = new GroupMember();
        $groupMember->setUser($user);
        $group->addGroupMember($groupMember);

        $manager->persist($groupMember);
        $manager->persist($group);

        //Alert the parent group that there is a new child to live update group lists
        if($group->discoverable && ($owner = $group->getOwner())) {
            $groupAddedEvent = Event::createEphemeralEvent();
            $groupAddedEvent->setEventGroup($owner);
            $groupAddedEvent->setUser($user);
            $groupAddedEvent->type = Event::TYPE_CHILD_GROUP_CREATED;

            $this->messageBus->dispatch($groupAddedEvent);
        }

        $manager->flush();
        $manager->refresh($groupMember);

        $this->messageBus->dispatch($groupMember);
    }
}