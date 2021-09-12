<?php
namespace EventStreamApi;

use ApiPlatform\Core\Bridge\Symfony\Messenger\RemoveStamp;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use EventStreamApi\Entity\Event;
use EventStreamApi\Entity\User;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

final class DataPersister implements ContextAwareDataPersisterInterface
{
    private DataPersisterInterface $decorated;
    private Security $security;
    private MessageBusInterface $messageBus;

    public function __construct(DataPersisterInterface $decorated, Security $security, MessageBusInterface $messageBus)
    {
        $this->decorated = $decorated;
        $this->security = $security;
        $this->messageBus = $messageBus;
    }

    /**
     * @phpstan-ignore-next-line
     */
    public function supports($data, array $context = []): bool
    {
        /**
         * @phpstan-ignore-next-line
         */
        return $this->decorated->supports($data, $context);
    }

    /**
     * @phpstan-ignore-next-line
     */
    public function persist($data, array $context = [])
    {
        // Pre save events go here, post save events/triggers/data happen as event handlers.
        if ($data instanceof Event) {
            $this->setDefaultEventFields($data);
        }

        $isEphemeralEvent = $data instanceof Event && $data->ephemeral;
        if($isEphemeralEvent) {
            $result = $data;
            $result->setId(Uuid::uuid4());
        } else {
            /**
             * @phpstan-ignore-next-line
             */
            $result = $this->decorated->persist($data, $context);
        }

        $this->messageBus->dispatch($result);

        return $result;
    }
//
//    private static function isCreateRequest(array $context): bool
//    {
//        return ($context['collection_operation_name'] ?? null) === 'post' ||
//               ($context['graphql_operation_name'] ?? null) === 'create';
//    }

    /**
     * @phpstan-ignore-next-line
     */
    public function remove($data, array $context = []): void
    {
        /**
         * @phpstan-ignore-next-line
         */
        $this->decorated->remove($data, $context);

        $this->messageBus->dispatch($data, [new RemoveStamp()]);
    }

    /**
     * @param Event $data
     */
    private function setDefaultEventFields(Event $data): void
    {
        //If the user wasn't set, and we are under the context of a user making an api request we should use that.
        if (!$data->getUser()) {
            /**
             * @var User|null $user
             */
            $user = $this->security->getUser();
            if (!$user) {
                throw new \RuntimeException("Unable to create event, no user set and not currently under a user context");
            }
            $data->setUser($user);
        }

        //Always set the time to server time, this ensures integrity if it was set some other way.
        $data->datetime = new \DateTimeImmutable();
    }
}