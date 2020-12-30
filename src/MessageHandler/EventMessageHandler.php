<?php
namespace EventStreamApi\MessageHandler;

use EventStreamApi\Entity\Event;
use EventStreamApi\Entity\StreamUser;
use EventStreamApi\Entity\Subscription;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EventMessageHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus) {
        $this->messageBus = $messageBus;
    }

    public function __invoke(Event $event): void
    {
        $subscriptionsByTransport = array_reduce(
            array_merge([], ...array_map(static function(StreamUser $streamUser) {
                return $streamUser->getSubscriptions();
            }, $event->getStream()->getStreamUsers())),
            static function($carry, Subscription $item) use ($event) {
                if($item->eventTypes && !in_array($event->type, $item->eventTypes, true)) {
                    return $carry;
                }

                if (!isset($carry[$item->transport])) {
                    $carry[$item->transport] = [];
                }
                $carry[$item->transport][] = $item;
                return $carry;
            },
            []
        );

        foreach($subscriptionsByTransport as $transportName => $subscriptions) {
            $this->messageBus->dispatch(
                new Notification($event, $subscriptions)
            );
        }
    }
}