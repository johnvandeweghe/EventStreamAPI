<?php

namespace PostChat\Api\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Interop\Queue\Context;
use PostChat\Api\Entity\Event;
use PostChat\Api\Entity\Stream;
use PostChat\Api\Entity\StreamUser;
use PostChat\Api\Entity\EventData\MessageEventData;
use PostChat\Api\Entity\Subscription;
use PostChat\Api\Entity\User;

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

        $johnsUser = new User("auth0|5f21eafcc13b130228f812b5");

        $streamUser1 = new StreamUser();
        $streamUser1->setUser($johnsUser);
        $streamUser1->setStream($workspace);

        $streamUser2 = new StreamUser();
        $streamUser2->setUser($johnsUser);
        $streamUser2->setStream($workspace2);

        $manager->persist($johnsUser);
        $manager->persist($streamUser1);
        $manager->persist($streamUser2);

        $manager->flush();

        $this->context->declareTopic($this->context->createTopic("transport-pusher"));
    }

    protected function createWorkspace(
        ObjectManager $manager,
        int $numChannels,
        int $numMockUsers,
        int $numMockEvents
    ): Stream {
        $workspace = new Stream();
        $workspace->name = $this->faker->company;
        $workspace->discoverable = false;

        $generalChannel = new Stream();
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
            $user->picture = "gravatar";

            $streamUserWorkspace = new StreamUser();
            $streamUserWorkspace->setUser($user);
            $streamUserWorkspace->setStream($workspace);

            $streamUserGeneral = new StreamUser();
            $streamUserGeneral->setUser($user);
            $streamUserGeneral->setStream($generalChannel);

            $manager->persist($user);
            $manager->persist($streamUserWorkspace);
            $manager->persist($streamUserGeneral);
        }

        //Generate random channels and add users randomly (2 to 20 users, or the mock user limit, whatever is smaller)
        $this->createChannels($manager, $workspace, $users, $numChannels, $numMockUsers, $numMockEvents, 20, true);

        //Setup some random 2-5 user groups with no name that are undiscoverable
        $this->createChannels($manager, $workspace, $users, (int)($numMockUsers / 3), $numMockUsers, $numMockEvents, 5, false);

        $manager->persist($workspace);
        $manager->persist($generalChannel);

        return $workspace;
    }

    protected function createChannels(ObjectManager $manager, Stream $workspace, array $users, int $numChannels, int $numMockUsers, int $numMockEvents, int $maxUsersPerChannel, bool $discoverable): void
    {
        foreach (range(1, $numChannels) as $i) {
            $channel = new Stream();
            $channel->name = $discoverable ? $this->faker->jobTitle : null;
            $channel->discoverable = $discoverable;
            $channel->setOwner($workspace);

            shuffle($users);

            $channelUsers = array_slice($users, 0, min($numMockUsers, random_int(2, $maxUsersPerChannel)));
            foreach ($channelUsers as $channelUser) {
                $streamUser = new StreamUser();
                $streamUser->setUser($channelUser);
                $streamUser->setStream($channel);

                $event = new Event();
                $event->setUser($channelUser);
                $event->setStream($channel);
                $event->type = Event::TYPE_USER_JOINED;
                $event->datetime = new \DateTimeImmutable("Jan 1st");

                if (random_int(0, 1) === 1) {
                    $subscription = new Subscription();
                    $subscription->transport = "pusher";
                    $subscription->setStreamUser($streamUser);

                    $manager->persist($subscription);
                }

                $manager->persist($event);
                $manager->persist($streamUser);
            }

            //Add events
            foreach (range(1, $numMockEvents) as $y) {
                $event = new Event();
                $event->setUser($channelUsers[array_rand($channelUsers)]);
                $event->setStream($channel);
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
