<?php
namespace PostChat\Api\Security;

use PostChat\Api\Entity\User;
use PostChat\Api\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Authenticator\Passport\UserPassportInterface;

class GuardAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private TokenVerifier $tokenVerifier,
        private ManagerRegistry $managerRegistry,
        private UserRepository $userRepository
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization') &&
            str_starts_with($request->headers->get('Authorization'), 'Bearer');
    }

    public function authenticate(Request $request): UserPassportInterface
    {
        $token = str_replace("Bearer ", "", $request->headers->get('Authorization'));

        try {
            $validatedToken = $this->tokenVerifier->verify($token);
        } catch (\Throwable $exception) {
            throw new CustomUserMessageAuthenticationException("Unable to validate JWT: " . $exception->getMessage(), [], $exception->getCode(), $exception);
        }

        $user = $this->userRepository->find($validatedToken["sub"]);

        if(!$user) {
            $user = $this->createUserFromRemoteUser($validatedToken["sub"]);
        }

        return new SelfValidatingPassport($user);
    }

    private function createUserFromRemoteUser($tokenSubject): User
    {
        $entityManager = $this->managerRegistry->getManagerForClass(User::class);

        if(!$entityManager) {
            //This shouldn't happen
            throw new \RuntimeException("Internal server error.");
        }

        $user = new User($tokenSubject);
        $entityManager->persist($user);

        $entityManager->flush();
        $entityManager->refresh($user);

        return $user;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }
}