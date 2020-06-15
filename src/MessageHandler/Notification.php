<?php
namespace Productively\Api\MessageHandler;

use Productively\Api\Entity\Event;
use Productively\Api\Entity\Subscription;

class Notification
{
    protected Event $event;

    /**
     * @var Subscription[]
     */
    protected array $subscriptions;

    /**
     * Notification constructor.
     * @param Event $event
     * @param array|Subscription[] $subscriptions
     */
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