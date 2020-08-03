<?php
namespace PostChat\Api\MessageHandler;

use Auth0\SDK\API\Management;
use PostChat\Api\Entity\Event;
use PostChat\Api\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UserHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;
    private Management $auth0;
    private LoggerInterface $logger;

    public function __construct(Management $auth0, MessageBusInterface $messageBus, LoggerInterface $logger)
    {
        $this->auth0 = $auth0;
        $this->messageBus = $messageBus;
        $this->logger = $logger;
    }

    public function __invoke(User $user)
    {
        try {
            $data = [];
            if($user->name !== null) {
                $data['name'] = $user->name;
            }
            if($user->email !== null) {
                $data['email'] = $user->email;
            }
            if($user->picture !== null) {
                $data['picture'] = $user->picture;
            }
            if($user->nickname !== null) {
                $data['nickname'] = $user->nickname;
            }

            $this->auth0->users()->update($user->getId(), $data);
        } catch (\Exception $exception) {
            $this->logger->error("Failed to save user data to Auth0: " . $exception->getMessage());
        }

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