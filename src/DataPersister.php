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
        /**
         * @var User|null $user
         */
        $user = $this->security->getUser();
        if(!$user) {
            return $data;
        }

        if ($data instanceof Event) {
            $data->setUser($user);
            $data->datetime = new \DateTimeImmutable();
        }

        $isEphemeralEvent = $data instanceof Event && $data->ephemeral;
        if(!$isEphemeralEvent) {
            /**
             * @phpstan-ignore-next-line
             */
            $result = $this->decorated->persist($data, $context);
        } else {
            $result = $data;
            $result->setId(Uuid::uuid4());
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
}