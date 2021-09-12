<?php
namespace EventStreamApi\MessageHandler;

use EventStreamApi\Repository\TransportRepository;
use EventStreamApi\Repository\UserRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class TransportEventHandler implements MessageHandlerInterface
{
    public function __construct(
        private TransportRepository $transportRepository,
        private UserRepository $userRepository
    ) { }

    public function __invoke(TransportEvent $transportEvent): void
    {
        if (!($eventTransport = $transportEvent->getEvent()->getTransport())) {
            // Ignore events missing a transport (misbehaving transport).
            return;
        }

        if (!($transport = $this->transportRepository->find($eventTransport->getName()))) {
            // Ignore events with an invalid transport (misbehaving transport).
            return;
        }

        if (!openssl_verify(
            $transportEvent->getEvent()->getId(),
            $transportEvent->getSignature(),
            $transport->publicKey
        )) {
            // Ignore events with an invalid signature (malicious transport).
            return;
        }

        if (!($user = $this->userRepository->find($transportEvent->getEvent()->getUser()->getId()))) {
            // TODO: Handle users that don't exist
            return;
        }

        // TODO: Handle events in streams that don't exist

        // TODO: Handle events in streams that the user doesn't belong to

        // Save event
    }
}