<?php
namespace EventStreamApi\MessageHandler;

use EventStreamApi\DataPersister;
use EventStreamApi\Entity\Event;
use EventStreamApi\Entity\Role;
use EventStreamApi\Entity\Stream;
use EventStreamApi\Entity\StreamUser;
use EventStreamApi\Entity\User;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\Security;

class StreamHandler implements MessageHandlerInterface
{
    public function __construct(
        private Security $security,
        private DataPersister $dataPersister
    ) {}

    public function __invoke(Stream $stream): void
    {
        /**
         * @var ?User $user
         */
        $user = $this->security->getUser();

        if (!$user || $stream->hasUser($user)){
            //Prevent triggering new stream behavior again, assume if the user is already in it it must be update event.
            return;
        }

        $streamUser = new StreamUser();
        $streamUser->setUser($user);
        $stream->addStreamUser($streamUser);

        self::setDefaultRoles($stream);

        $streamUser->addRole($stream->getDefaultCreatorRole());

        $this->dataPersister->persist($streamUser);

        //Alert the parent stream that there is a new child to live update stream lists
        if($stream->discoverable && ($owner = $stream->getOwner())) {
            $streamAddedEvent = new Event();
            $streamAddedEvent->setStream($owner);
            $streamAddedEvent->type = Event::MARK_CHILD_STREAM_CREATED;
            $streamAddedEvent->ephemeral = true;
            $this->dataPersister->persist($streamAddedEvent);
        }
    }

    /**
     * @param Stream $stream
     */
    protected static function setDefaultRoles(Stream $stream): void
    {
        if ($owner = $stream->getOwner()) {
            $stream->setDefaultCreatorRole($owner->getDefaultCreatorRole());
            $stream->setDefaultUserRole($owner->getDefaultUserRole());
            $stream->setDefaultBotRole($owner->getDefaultBotRole());
        } else {
            //Create new default roles for this root stream
            $adminRole = new Role();
            $adminRole->name = "Admin";
            $adminRole->streamArchive = true;
            $adminRole->streamCreate = true;
            $adminRole->streamRoles = true;
            $adminRole->streamEdit = true;
            $adminRole->streamAccess = true;
            $adminRole->streamInvite = true;
            $adminRole->streamJoin = true;
            $adminRole->streamWrite = true;
            $adminRole->streamRead = true;

            $userRole = new Role();
            $userRole->name = "User";
            $userRole->streamCreate = true;
            $userRole->streamJoin = true;
            $userRole->streamWrite = true;
            $userRole->streamRead = true;

            $botRole = new Role();
            $botRole->name = "Bot";
            $botRole->streamCreate = true;
            $botRole->streamWrite = true;
            $botRole->streamRead = true;

            $stream->addRole($adminRole);
            $stream->addRole($userRole);
            $stream->addRole($botRole);

            $stream->setDefaultCreatorRole($adminRole);
            $stream->setDefaultUserRole($userRole);
            $stream->setDefaultBotRole($botRole);
        }
    }

}