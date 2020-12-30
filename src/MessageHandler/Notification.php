<?php
namespace EventStreamApi\MessageHandler;

use EventStreamApi\Entity\Event;
use EventStreamApi\Entity\Subscription;

class Notification
{
    protected Event $event;

    /**
     * @var Subscription[]
     */
    protected array $subscriptions;

    /**
     * @param Event $event
     * @param Subscription[] $subscriptions
     */
    public function __construct(Event $event, array $subscriptions)
    {
        $this->event = $event;
        $this->subscriptions = $subscriptions;
    }

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