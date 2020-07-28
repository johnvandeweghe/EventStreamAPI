<?php
namespace PostChat\Api\Security;

use Auth0\SDK\Exception\InvalidTokenException;
use Auth0\SDK\Helpers\Tokens\TokenVerifier;
use PHPUnit\Framework\TestCase;
use PostChat\Api\Repository\UserRepository;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class GuardAuthenticatorTest extends TestCase
{

    public function testSupportsReturnsFalseWithoutAuthorization()
    {
        $tokenVerifier = $this->getMockBuilder(TokenVerifier::class)->disableOriginalConstructor()->getMock();
        $mangerRegistry = $this->getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->getMock();
        $userRepository = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->getMock();

        $authenticator = new GuardAuthenticator($tokenVerifier, $mangerRegistry, $userRepository);

        $request = Request::create('/', Request::METHOD_GET);

        self::assertFalse($authenticator->supports($request));
    }

    public function testSupportsReturnsFalseWithNonBearerAuth()
    {
        $tokenVerifier = $this->getMockBuilder(TokenVerifier::class)->disableOriginalConstructor()->getMock();
        $mangerRegistry = $this->getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->getMock();
        $userRepository = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->getMock();

        $authenticator = new GuardAuthenticator($tokenVerifier, $mangerRegistry, $userRepository);

        $request = Request::create('/', Request::METHOD_GET, [], [], [], [
            'HTTP_AUTHORIZATION' => "this is not a bearer token"
        ]);

        self::assertFalse($authenticator->supports($request));
    }

    public function testSupportsReturnsTrueWithBearerAuth()
    {
        $tokenVerifier = $this->getMockBuilder(TokenVerifier::class)->disableOriginalConstructor()->getMock();
        $mangerRegistry = $this->getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->getMock();
        $userRepository = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->getMock();

        $authenticator = new GuardAuthenticator($tokenVerifier, $mangerRegistry, $userRepository);

        $request = Request::create('/', Request::METHOD_GET, [], [], [], [
            'HTTP_AUTHORIZATION' => "Bearer opaquetoken"
        ]);

        self::assertTrue($authenticator->supports($request));
    }

    //It's easiest to quickly test the token extraction with this code path so they are combined.
    public function testAuthenticateExtractsTokenToVerifierAndRetrowsInvalidToken()
    {
        $tokenVerifier = $this->getMockBuilder(TokenVerifier::class)->disableOriginalConstructor()->getMock();
        $mangerRegistry = $this->getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->getMock();
        $userRepository = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->getMock();

        $authenticator = new GuardAuthenticator($tokenVerifier, $mangerRegistry, $userRepository);

        $token = "this is an arbitrary token";

        $request = Request::create('/', Request::METHOD_GET, [], [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $token"
        ]);

        $this->expectException(CustomUserMessageAuthenticationException::class);

        $tokenVerifier->expects(self::once())->method('verify')->with($token)
            ->willThrowException(new InvalidTokenException());

        $authenticator->authenticate($request);
    }

    //TODO: test authenticate method


    public function testUnusedMethodsReturnNull()
    {
        $tokenVerifier = $this->getMockBuilder(TokenVerifier::class)->disableOriginalConstructor()->getMock();
        $mangerRegistry = $this->getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->getMock();
        $userRepository = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->getMock();

        $authenticator = new GuardAuthenticator($tokenVerifier, $mangerRegistry, $userRepository);

        $request = new Request();

        self::assertNull($authenticator->onAuthenticationSuccess($request, new TestBrowserToken(), ''));
        self::assertNull($authenticator->onAuthenticationFailure($request, new AuthenticationException()));
    }
}