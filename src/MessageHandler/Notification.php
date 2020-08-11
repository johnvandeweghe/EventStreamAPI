<?php
namespace PostChat\Api\MessageHandler;

use PostChat\Api\Entity\Event;
use PostChat\Api\Entity\Subscription;

class Notification
{
    protected Event $event;

    /**
     * @var Subscription[]
     */
    protected array $subscriptions;

    public function __construct(Event $event, $subscriptions)
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