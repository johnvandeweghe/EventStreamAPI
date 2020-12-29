<?php

namespace EventStreamApi\Security\Voter;

use EventStreamApi\Entity\StreamUser;
use EventStreamApi\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class StreamUserVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        return $attribute === 'STREAM_JOIN'
            && $subject instanceof StreamUser;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User || !$subject instanceof StreamUser) {
            return false;
        }

        //Enforce that to join a private stream you must have an unused invite in the stream.
        if ($subject->getStream()->private) {
            if (
                ($invite = $subject->getInvite()) &&
                $invite->getInvitedStreamUser() === null &&
                $invite->expiration > new \DateTimeImmutable() &&
                $invite->getStream()->getId() === $subject->getStream()->getId()
            ) {
                return true;
            }

            //TODO: Allow users with "allowed roles" to access as well.

            return false;
        }

        return true;

    }
}
