<?php
namespace EventStreamApi\MessageHandler;

use EventStreamApi\Entity\Event;
use EventStreamApi\Entity\Subscription;

class Notification
{
    /**
     * @param Event $event
     * @param Subscription[] $subscriptions
     */
    public function __construct(protected Event $event, protected array $subscriptions){}

    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @return Subscription[]
     */
    public function getSubscriptions(): array
    {
        return $this->subscriptions;
    }
}