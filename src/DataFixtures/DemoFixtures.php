<?php

namespace Productively\Api\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Interop\Queue\Context;
use Productively\Api\Entity\Event;
use Productively\Api\Entity\Group;
use Productively\Api\Entity\GroupMember;
use Productively\Api\Entity\MessageEventData;
use Productively\Api\Entity\Subscription;
use Productively\Api\Entity\User;

class DemoFixtures extends Fixture
{
    private Context $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function load(ObjectManager $manager): void
    {
        $group = new Group();
        $group->name = "Demo Server";

        $generalChannel = new Group();
        $generalChannel->setOwner($group);
        $generalChannel->name = "General";
        $generalChannel->discoverable = true;

        $secretChannel = new Group();
        $secretChannel->setOwner($group);
        $secretChannel->name = "Not General";
        $secretChannel->discoverable = false;

        $userA = new User("auth0|5eb51dd31cc1ac0c1493050e");
        $userB = new User("mock|fakeIdentifier");

        $groupMember = new GroupMember();
        $groupMember->setUserGroup($group);
        $groupMember->setUser($userA);

        $groupMember2 = new GroupMember();
        $groupMember2->setUserGroup($generalChannel);
        $groupMember2->setUser($userA);

        $sub = new Subscription();
        $sub->setGroupMember($groupMember2);
        $sub->transport = "pusher";

        $event = new Event();
        $event->datetime = new \DateTimeImmutable();
        $event->setEventGroup($generalChannel);
        $event->type = Event::TYPE_MESSAGE;
        $event->setUser($userA);

        $messageData = new MessageEventData();
        $messageData->text = "This is a test message";
        $event->setMessageEventData($messageData);

        $otherEvent = new Event();
        $otherEvent->datetime = new \DateTimeImmutable();
        $otherEvent->setEventGroup($secretChannel);
        $otherEvent->type = Event::TYPE_MESSAGE;
        $otherEvent->setUser($userB);

        $messageData2 = new MessageEventData();
        $messageData2->text = "This is a secret test message";
        $otherEvent->setMessageEventData($messageData2);

        $manager->persist($userA);
        $manager->persist($userB);
        $manager->persist($group);
        $manager->persist($generalChannel);
        $manager->persist($secretChannel);
        $manager->persist($groupMember);
        $manager->persist($groupMember2);
        $manager->persist($sub);
        $manager->persist($event);
        $manager->persist($otherEvent);

        $manager->flush();

        $this->context->declareTopic($this->context->createTopic("transport-pusher"));
    }
}
