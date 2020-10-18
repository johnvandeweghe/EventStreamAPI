<?php
namespace PostChat\Api\Security;

use Auth0\SDK\API\Management;
use Auth0\SDK\Exception\InvalidTokenException;
use Auth0\SDK\Helpers\Tokens\TokenVerifier;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use PostChat\Api\Entity\User;
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
        $management = $this->getMockBuilder(Management::class)->disableOriginalConstructor()->getMock();

        $authenticator = new GuardAuthenticator($tokenVerifier, $mangerRegistry, $userRepository, $management);

        $request = Request::create('/', Request::METHOD_GET);

        self::assertFalse($authenticator->supports($request));
    }

    public function testSupportsReturnsFalseWithNonBearerAuth()
    {
        $tokenVerifier = $this->getMockBuilder(TokenVerifier::class)->disableOriginalConstructor()->getMock();
        $mangerRegistry = $this->getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->getMock();
        $userRepository = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->getMock();
        $management = $this->getMockBuilder(Management::class)->disableOriginalConstructor()->getMock();

        $authenticator = new GuardAuthenticator($tokenVerifier, $mangerRegistry, $userRepository, $management);

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
        $management = $this->getMockBuilder(Management::class)->disableOriginalConstructor()->getMock();

        $authenticator = new GuardAuthenticator($tokenVerifier, $mangerRegistry, $userRepository, $management);

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
        $management = $this->getMockBuilder(Management::class)->disableOriginalConstructor()->getMock();

        $authenticator = new GuardAuthenticator($tokenVerifier, $mangerRegistry, $userRepository, $management);

        $token = "this is an arbitrary token";

        $request = Request::create('/', Request::METHOD_GET, [], [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $token"
        ]);

        $this->expectException(CustomUserMessageAuthenticationException::class);

        $tokenVerifier->expects(self::once())->method('verify')->with($token)
            ->willThrowException(new InvalidTokenException());

        $authenticator->authenticate($request);
    }

    public function testCreatesUserWithFieldsFromAuth0()
    {
        $validatedToken = [
            'sub' => 'mock|er4ewuoth432',
            'name' => 'John Tester',
            'nickname' => 'John',
            'picture' => 'base64:asdweirfweiurth==',
            'email' => 'john@getpostchat.com'
        ];

        $auth0User = [
            'user_id' => 'mock|er4ewuoth432',
            'name' => 'John Tester',
            'nickname' => 'John',
            'picture' => 'base64:asdweirfweiurth==',
            'email' => 'john@getpostchat.com'
        ];

        $tokenVerifier = $this->getMockBuilder(TokenVerifier::class)->disableOriginalConstructor()->getMock();
        $mangerRegistry = $this->getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->getMock();
        $userRepository = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->setMethods(['find'])->getMock();
        $entityManager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $management = $this->getMockBuilder(Management::class)->disableOriginalConstructor()->getMock();
        $users = $this->getMockBuilder(Management\Users::class)->disableOriginalConstructor()->getMock();
        $tokenVerifier->method('verify')->willReturn($validatedToken);
        $mangerRegistry->method('getManagerForClass')->with(User::class)->willReturn($entityManager);


        $authenticator = new GuardAuthenticator($tokenVerifier, $mangerRegistry, $userRepository, $management);

        $request = Request::create('/', Request::METHOD_GET, [], [], [], [
            'HTTP_AUTHORIZATION' => "Bearer anytoken"
        ]);

        $userRepository->expects(self::once())->method('find')->with($validatedToken['sub'])->willReturn(null);

        $management->expects(self::once())->method('users')->willReturn($users);
        $users->expects(self::once())->method('get')->with($validatedToken['sub'])->willReturn($auth0User);

        $entityManager->expects(self::once())->method('persist')->willReturnCallback(function($user) use ($validatedToken) {
           self::assertEquals(User::class, get_class($user));
           /** @var $user User */
           self::assertEquals($validatedToken['sub'], $user->getId());
        });
        $entityManager->expects(self::once())->method('flush');
        $entityManager->expects(self::once())->method('refresh')->willReturnCallback(function($user) use ($validatedToken) {
            self::assertEquals(User::class, get_class($user));
            /** @var $user User */
            self::assertEquals($validatedToken['sub'], $user->getId());
        });

        $passport = $authenticator->authenticate($request);

        $user = $passport->getUser();
        self::assertEquals($validatedToken['sub'], $user->getUsername());
        self::assertInstanceOf(User::class, $user);
        /** @var $user User */
        self::assertEquals($validatedToken['name'], $user->name);
        self::assertEquals($validatedToken['nickname'], $user->nickname);
        self::assertEquals($validatedToken['picture'], $user->picture);
        self::assertEquals($validatedToken['email'], $user->email);
    }

    public function testDoesntUpdateUserIfFound()
    {
        $validatedToken = [
            'sub' => 'mock|er4ewuoth432',
            'name' => 'John Tester',
            'nickname' => 'John',
            'picture' => 'base64:asdweirfweiurth==',
            'email' => 'john@getpostchat.com'
        ];

        $expectedUser = new User($validatedToken['sub']);

        $tokenVerifier = $this->getMockBuilder(TokenVerifier::class)->disableOriginalConstructor()->getMock();
        $mangerRegistry = $this->getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->getMock();
        $userRepository = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->setMethods(['find'])->getMock();
        $entityManager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $management = $this->getMockBuilder(Management::class)->disableOriginalConstructor()->getMock();
        $tokenVerifier->method('verify')->willReturn($validatedToken);
        $mangerRegistry->method('getManagerForClass')->with(User::class)->willReturn($entityManager);


        $authenticator = new GuardAuthenticator($tokenVerifier, $mangerRegistry, $userRepository, $management);

        $request = Request::create('/', Request::METHOD_GET, [], [], [], [
            'HTTP_AUTHORIZATION' => "Bearer anytoken"
        ]);

        $userRepository->expects(self::once())->method('find')->with($validatedToken['sub'])->willReturn($expectedUser);
        $entityManager->expects(self::never())->method('persist');
        $entityManager->expects(self::never())->method('flush');
        $entityManager->expects(self::never())->method('refresh');

        $passport = $authenticator->authenticate($request);

        $user = $passport->getUser();
        self::assertEquals($expectedUser, $user);
    }

    public function testUnusedMethodsReturnNull()
    {
        $tokenVerifier = $this->getMockBuilder(TokenVerifier::class)->disableOriginalConstructor()->getMock();
        $mangerRegistry = $this->getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->getMock();
        $userRepository = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->getMock();
        $management = $this->getMockBuilder(Management::class)->disableOriginalConstructor()->getMock();

        $authenticator = new GuardAuthenticator($tokenVerifier, $mangerRegistry, $userRepository, $management);

        $request = new Request();

        self::assertNull($authenticator->onAuthenticationSuccess($request, new TestBrowserToken(), ''));
        self::assertNull($authenticator->onAuthenticationFailure($request, new AuthenticationException()));
    }
}