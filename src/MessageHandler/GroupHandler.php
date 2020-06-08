<?php
namespace Productively\Api\MessageHandler;

use Productively\Api\Entity\Group;
use Productively\Api\Entity\GroupMember;
use Productively\Api\Entity\User;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Persistence\ManagerRegistry;

class GroupHandler implements MessageHandlerInterface
{
    private Security $security;
    /**
     * @var ManagerRegistry
     */
    private ManagerRegistry $managerRegistry;

    public function __construct(Security $security, ManagerRegistry $managerRegistry)
    {
        $this->security = $security;
        $this->managerRegistry = $managerRegistry;
    }

    public function __invoke(Group $group)
    {
        $manager = $this->managerRegistry->getManagerForClass(get_class($group));
        /**
         * @var $user User
         */
        if(!$manager || !($user = $this->security->getUser())){
            return;
        }

        $groupMember = new GroupMember();
        $groupMember->setUser($user);
        $group->addGroupMember($groupMember);

        $manager->persist($groupMember);
        $manager->persist($group);

        $manager->flush();
        //
    }
}