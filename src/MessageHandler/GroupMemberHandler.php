<?php
namespace Productively\Api\MessageHandler;

use Productively\Api\Entity\Event;
use Productively\Api\Entity\GroupMember;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

class GroupMemberHandler implements MessageHandlerInterface
{
    /**
     * @var ManagerRegistry
     */
    private ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function __invoke(GroupMember $member)
    {
        $manager = $this->managerRegistry->getManagerForClass(get_class($member));
        if(!$manager){
            return;
        }

        $deleted = !in_array($member, $member->getUser()->getGroupMembers());

        $event = new Event();
        $event->setUser($member->getUser());
        $event->setEventGroup($member->getUserGroup());
        $event->type = $deleted ? Event::TYPE_GROUP_LEFT : Event::TYPE_GROUP_JOINED;

        $manager->persist($event);
        $manager->flush();
    }
}