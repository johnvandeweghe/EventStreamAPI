<?php

namespace Productively\Api\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Productively\Api\Entity\Event;
use Productively\Api\Entity\Group;
use Productively\Api\Entity\GroupMember;
use Productively\Api\Entity\MessageEventData;
use Productively\Api\Entity\Subscription;

class DemoFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $group = new Group();
        $group->name = "Demo Server";

        $generalChannel = new Group();
        $generalChannel->setOwner($group);
        $generalChannel->name = "General";

        $secretChannel = new Group();
        $secretChannel->setOwner($group);
        $secretChannel->name = "Not General";

        $userId = "auth0|5eb51dd31cc1ac0c1493050e";

        $groupMember = new GroupMember();
        $groupMember->setUserGroup($group);
        $groupMember->userIdentifier = $userId;

        $groupMember2 = new GroupMember();
        $groupMember2->setUserGroup($generalChannel);
        $groupMember2->userIdentifier = $userId;

        $sub = new Subscription();
        $sub->setGroupMember($groupMember2);
        $sub->transport = "webhook";

        $event = new Event();
        $event->datetime = new \DateTimeImmutable();
        $event->setEventGroup($generalChannel);
        $event->type = Event::TYPE_MESSAGE;
        $event->userIdentifier = $userId;

        $messageData = new MessageEventData();
        $messageData->text = "This is a test message";
        $event->setMessageEventData($messageData);

        $otherEvent = new Event();
        $otherEvent->datetime = new \DateTimeImmutable();
        $otherEvent->setEventGroup($secretChannel);
        $otherEvent->type = Event::TYPE_MESSAGE;
        $otherEvent->userIdentifier = "RADFNWOUIERFHBUIERFGH";

        $messageData = new MessageEventData();
        $messageData->text = "This is a secret test message";
        $otherEvent->setMessageEventData($messageData);

        $manager->persist($group);
        $manager->persist($generalChannel);
        $manager->persist($secretChannel);
        $manager->persist($groupMember);
        $manager->persist($groupMember2);
        $manager->persist($sub);
        $manager->persist($event);
        $manager->persist($otherEvent);

        $manager->flush();
    }
}
