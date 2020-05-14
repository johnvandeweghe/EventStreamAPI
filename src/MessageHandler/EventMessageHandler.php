<?php
namespace Productively\Api\MessageHandler;

use ApiPlatform\Core\JsonApi\Serializer\ObjectNormalizer;
use Productively\Api\Entity\Event;
use Productively\Api\Entity\GroupMember;
use Productively\Api\Entity\Subscription;
use Psr\Log\LoggerInterface;
use Pusher\Pusher;
use Pusher\PusherException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventMessageHandler implements MessageHandlerInterface
{
    private Pusher $pusher;
    private NormalizerInterface $normalizer;
    private LoggerInterface $logger;

    public function __construct(Pusher $pusher, NormalizerInterface $normalizer, LoggerInterface $logger)
    {
        $this->pusher = $pusher;
        $this->normalizer = $normalizer;
        $this->logger = $logger;
    }

    public function __invoke(Event $event)
    {
        $subscriptionsByTransport = array_reduce(
            array_merge([], ...array_map(static function(GroupMember $groupMember) {
                return $groupMember->getSubscriptions();
            }, $event->getEventGroup()->getGroupMembers())),
            static function($carry, Subscription $item) {
                if (!isset($carry[$item->transport])) {
                    $carry[$item->transport] = [];
                }
                $carry[$item->transport][] = $item;
                return $carry;
            },
            []
        );

        //TODO: Ship event to each transport pubsub topic with list of subs.

        if (isset($subscriptionsByTransport['pusher'])) {
            try {
                $this->pusher->trigger(
                    "group-" . $event->getEventGroup()->getId(),
                    $event->type,
                    $this->normalizer->normalize($event, ObjectNormalizer::FORMAT)
                );
            } catch (PusherException $e) {
                $this->logger->critical("Unable to deliver event to pusher: {pusherMessage}", [
                    "pusherMessage" => $e->getMessage()
                ]);
            } catch (ExceptionInterface $e) {
                $this->logger->critical("Unable to serialize message: {errorMessage}", [
                    "errorMessage" => $e->getMessage()
                ]);
            }
        }
    }
}