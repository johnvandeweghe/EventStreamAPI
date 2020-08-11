<?php
namespace PostChat\Api\MessageHandler;

use Enqueue\MessengerAdapter\EnvelopeItem\TransportConfiguration;
use PostChat\Api\Entity\Event;
use PostChat\Api\Entity\StreamUser;
use PostChat\Api\Entity\Subscription;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EventMessageHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus) {
        $this->messageBus = $messageBus;
    }

    public function __invoke(Event $event)
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
                new Notification($event, $subscriptions),
                [(new TransportConfiguration())->setTopic("transport-$transportName")]
            );
        }
    }
}