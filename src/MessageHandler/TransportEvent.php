<?php
namespace EventStreamApi\MessageHandler;

use EventStreamApi\Entity\Event;

class TransportEvent
{
    public function __construct(protected Event $event, private string $signature){}

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getSignature(): string
    {
        return $this->signature;
    }
}