<?php
namespace EventStreamApi\MessageHandler;

use EventStreamApi\Entity\Event;
use EventStreamApi\Entity\EventData\MarkerEventData;
use EventStreamApi\Entity\Role;
use EventStreamApi\Entity\Stream;
use EventStreamApi\Entity\StreamUser;
use EventStreamApi\Entity\User;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Doctrine\Persistence\ManagerRegistry;

class StreamHandler implements MessageHandlerInterface
{
    private Security $security;
    private ManagerRegistry $managerRegistry;
    private MessageBusInterface $messageBus;

    public function __construct(Security $security, ManagerRegistry $managerRegistry, MessageBusInterface $messageBus)
    {
        $this->security = $security;
        $this->managerRegistry = $managerRegistry;
        $this->messageBus = $messageBus;
    }

    public function __invoke(Stream $stream)
    {
        $manager = $this->managerRegistry->getManagerForClass(Stream::class);
        /**
         * @var $user User
         */
        if(!$manager || !($user = $this->security->getUser()) || $stream->hasUser($user)){
            //Prevent triggering new stream behavior again, assume if the user is already in it it must be update event.
            return;
        }

        $streamUser = new StreamUser();
        $streamUser->setUser($user);
        $stream->addStreamUser($streamUser);

        self::setDefaultRoles($stream);

        $streamUser->addRole($stream->getDefaultCreatorRole());

        $manager->persist($streamUser);
        $manager->persist($stream);

        //Alert the parent stream that there is a new child to live update stream lists
        if($stream->discoverable && ($owner = $stream->getOwner())) {
            $this->alertParentOfNewChild($owner, $user);
        }

        $manager->flush();
        $manager->refresh($streamUser);

        $this->messageBus->dispatch($streamUser);
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

    /**
     * @param Stream $owner
     * @param User $user
     */
    protected function alertParentOfNewChild(Stream $owner, User $user): void
    {
        $streamAddedEvent = Event::createEphemeralMarkerEvent(MarkerEventData::MARK_CHILD_STREAM_CREATED);
        $streamAddedEvent->setStream($owner);
        $streamAddedEvent->setUser($user);

        $this->messageBus->dispatch($streamAddedEvent);
    }
}