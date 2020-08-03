<?php
namespace PostChat\Api\MessageHandler;

use Auth0\SDK\Auth0;
use PostChat\Api\Entity\Event;
use PostChat\Api\Entity\User;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UserHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(Auth0 $auth0, MessageBusInterface $messageBus)
    {
        $auth0->
        $this->messageBus = $messageBus;
    }

    public function __invoke(User $user)
    {
        //TODO: Update auth0 using management api

        //Fire an event to each workspace the user belongs to

        foreach($user->getGroupMembers() as $groupMember) {
            $userGroup = $groupMember->getUserGroup();
            if($userGroup->getOwner() !== null) {
                continue;
            }
            $userUpdatedEvent = Event::createEphemeralEvent();
            $userUpdatedEvent->setEventGroup($userGroup);
            $userUpdatedEvent->setUser($user);
            $userUpdatedEvent->type = Event::TYPE_USER_UPDATED;

            $this->messageBus->dispatch($userUpdatedEvent);
        }
    }
}