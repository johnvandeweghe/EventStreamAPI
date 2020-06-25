<?php

namespace Productively\Api\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
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
    private Generator $faker;

    public function __construct(Context $context)
    {
        $this->context = $context;
        $this->faker = \Faker\Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        $workspace = $this->createWorkspace($manager, 10, 100, 100);

        $workspace2 = $this->createWorkspace($manager, 10, 100, 100);

        //Extra workspace for access control tests
        $this->createWorkspace($manager, 10, 100, 100);

        $johnsUser = new User("auth0|5eb51dd31cc1ac0c1493050e");

        $groupMember1 = new GroupMember();
        $groupMember1->setUser($johnsUser);
        $groupMember1->setUserGroup($workspace);

        $groupMember2 = new GroupMember();
        $groupMember2->setUser($johnsUser);
        $groupMember2->setUserGroup($workspace2);

        $manager->persist($johnsUser);
        $manager->persist($groupMember1);
        $manager->persist($groupMember2);

        $manager->flush();

        $this->context->declareTopic($this->context->createTopic("transport-pusher"));
    }

    protected function createWorkspace(
        ObjectManager $manager,
        int $numChannels,
        int $numMockUsers,
        int $numMockEvents
    ): Group {
        $workspace = new Group();
        $workspace->name = $this->faker->company;
        $workspace->discoverable = false;

        $generalChannel = new Group();
        $generalChannel->setOwner($workspace);
        $generalChannel->name = "General";
        $generalChannel->discoverable = true;

        //Generate all the users
        $users = array_map(fn ($i) => new User("mock|" . $this->faker->uuid), range(1, $numMockUsers));

        //add all of the users to the workspace and the general channel, and persist them
        foreach($users as $user) {
            $user->nickname = $this->faker->firstName;
            $user->name = $user->nickname . " " . $this->faker->lastName;
            $user->email = $this->faker->email;

            $groupMemberWorkspace = new GroupMember();
            $groupMemberWorkspace->setUser($user);
            $groupMemberWorkspace->setUserGroup($workspace);

            $groupMemberGeneral = new GroupMember();
            $groupMemberGeneral->setUser($user);
            $groupMemberGeneral->setUserGroup($generalChannel);

            $manager->persist($user);
            $manager->persist($groupMemberWorkspace);
            $manager->persist($groupMemberGeneral);
        }

        //Generate random channels and add users randomly (2 to 20 users, or the mock user limit, whatever is smaller)
        $this->createChannels($manager, $workspace, $users, $numChannels, $numMockUsers, $numMockEvents, 20, true);

        //Setup some random 2-5 user groups with no name that are undiscoverable
        $this->createChannels($manager, $workspace, $users, (int)($numMockUsers / 3), $numMockUsers, $numMockEvents, 5, false);

        $manager->persist($workspace);
        $manager->persist($generalChannel);

        return $workspace;
    }

    protected function createChannels(ObjectManager $manager, Group $workspace, array $users, int $numChannels, int $numMockUsers, int $numMockEvents, int $maxUsersPerChannel, bool $discoverable): void
    {
        foreach (range(1, $numChannels) as $i) {
            $channel = new Group();
            $channel->name = $discoverable ? $this->faker->jobTitle : null;
            $channel->discoverable = $discoverable;
            $channel->setOwner($workspace);

            shuffle($users);

            $channelUsers = array_slice($users, 0, min($numMockUsers, random_int(2, $maxUsersPerChannel)));
            foreach ($channelUsers as $channelUser) {
                $groupMember = new GroupMember();
                $groupMember->setUser($channelUser);
                $groupMember->setUserGroup($channel);

                $event = new Event();
                $event->setUser($channelUser);
                $event->setEventGroup($channel);
                $event->type = Event::TYPE_USER_JOINED;
                $event->datetime = new \DateTimeImmutable("Jan 1st");

                if (random_int(0, 1) === 1) {
                    $subscription = new Subscription();
                    $subscription->transport = "pusher";
                    $subscription->setGroupMember($groupMember);

                    $manager->persist($subscription);
                }

                $manager->persist($event);
                $manager->persist($groupMember);
            }

            //Add events
            foreach (range(1, $numMockEvents) as $y) {
                $event = new Event();
                $event->setUser($channelUsers[array_rand($channelUsers)]);
                $event->setEventGroup($channel);
                $event->type = Event::TYPE_MESSAGE;
                $event->datetime = \DateTimeImmutable::createFromMutable($this->faker->dateTimeThisYear);

                $messageEventData = new MessageEventData();
                $messageEventData->text = $this->faker->realText();
                $event->setMessageEventData($messageEventData);

                $manager->persist($event);
            }

            $manager->persist($channel);
        }
    }
}
