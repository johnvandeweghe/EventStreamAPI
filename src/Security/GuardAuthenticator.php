<?php
namespace Productively\Api\Security;

use Auth0\SDK\Exception\InvalidTokenException;
use Auth0\SDK\Helpers\Tokens\TokenVerifier;
use Productively\Api\Entity\User;
use Productively\Api\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Doctrine\Common\Persistence\ManagerRegistry;

class GuardAuthenticator extends AbstractAuthenticator
{
    private TokenVerifier $tokenVerifier;
    private ManagerRegistry $managerRegistry;
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    public function __construct(TokenVerifier $tokenVerifier, ManagerRegistry $managerRegistry, UserRepository $userRepository)
    {
        $this->tokenVerifier = $tokenVerifier;
        $this->managerRegistry = $managerRegistry;
        $this->userRepository = $userRepository;
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization') &&
            strpos($request->headers->get('Authorization'), 'Bearer') === 0;
    }

    public function authenticate(Request $request): PassportInterface
    {
        $token = str_replace("Bearer ", "", $request->headers->get('Authorization'));

        try {
            $validatedToken = $this->tokenVerifier->verify($token);
        } catch (InvalidTokenException $exception) {
            throw new CustomUserMessageAuthenticationException("Unable to validate JWT: " . $exception->getMessage(), [], $exception->getCode(), $exception);
        }

        $entityManager = $this->managerRegistry->getManagerForClass(User::class);

        if(!$entityManager) {
            throw new AuthenticationException("Internal server error.");
        }

        $user = $this->userRepository->find($validatedToken["sub"]);

        if(!$user) {
            $user = new User($validatedToken["sub"]);
            $entityManager->persist($user);
        }


        $user->name = $validatedToken["name"] ?? null;
        $user->nickname = $validatedToken["nickname"] ?? null;
        $user->picture = $validatedToken["picture"] ?? null;
        $user->email = $validatedToken["email"] ?? null;

        $entityManager->flush();
        $entityManager->refresh($user);

        return new SelfValidatingPassport($user);
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