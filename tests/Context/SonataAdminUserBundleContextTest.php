<?php

/*
 * This file is part of the SonataAdminBehatContext package.
 *
 * (c) Oskar Stark <oskarstark@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\OStark\Context;

use Behat\Mink\Mink;
use FOS\UserBundle\Model\UserInterface;
use OStark\Context\SonataAdminUserBundleContext;
use OStark\Test\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Sonata\UserBundle\Entity\BaseUser;
use Sonata\UserBundle\Model\UserManagerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class SonataAdminUserBundleContextTest extends BaseTestCase
{
    /**
     * @var SonataAdminUserBundleContext
     */
    private $context;

    /**
     * @var MockObject&UserManagerInterface
     */
    private $userManager;

    /**
     * @var MockObject&Session
     */
    private $session;

    /**
     * @var MockObject&TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var Mink
     */
    private $mink;

    protected function setUp()
    {
        $this->session = new Session(new MockArraySessionStorage());
        $this->tokenStorage = new TokenStorage();

        $container = new Container();
        $container->setParameter('fos_user.firewall_name', 'foo');

        $this->context = new SonataAdminUserBundleContext(
            $this->userManager = $this->createMock(\Sonata\UserBundle\Entity\UserManager::class),
            $this->tokenStorage,
            $this->session,
            $container
        );

        $this->mink = self::setupMink('<p/>');
        $this->context->setMink($this->mink);
    }

    /**
     * @test
     */
    public function deleteLastCreatedUser()
    {
        $users = [$this->createMock(UserInterface::class)];
        $this->userManager
            ->expects(self::once())
            ->method('findBy')
            ->with([], ['createdAt' => 'DESC'], 1)
            ->willReturn($users);

        $this->userManager
            ->expects(self::once())
            ->method('deleteUser')
            ->with($users[0]);

        $this->context->deleteLastCreatedUser();
    }

    /**
     * @test
     */
    public function iAmAnAuthenticatedUser()
    {
        $user = new BaseUser();

        $this->userManager
            ->expects(self::once())
            ->method('createUser')
            ->willReturn($user);

        $this->userManager
            ->expects(self::once())
            ->method('updateUser')
            ->with($user);

        $this->context->iAmAnAuthenticatedUser();

        self::assertSame('test@example.com', $user->getUsername());
        $this->assertSessionDataValid($user);
    }

    /**
     * @test
     */
    public function iHaveRole()
    {
        $user = new BaseUser();

        $this->userManager
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['username' => 'test@example.com'])
            ->willReturn($user);

        $this->userManager
            ->expects(self::never())
            ->method('createUser');

        $this->userManager
            ->expects(self::once())
            ->method('updateUser')
            ->with($user);

        $this->context->iHaveRole('foo');

        self::assertSame(['FOO', 'ROLE_USER'], $user->getRoles());

        $this->assertSessionDataValid($user);
    }

    /**
     * @test
     */
    public function iAmAuthenticatedAsUser()
    {
        $user = new BaseUser();

        $this->userManager
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['username' => 'me@example.com'])
            ->willReturn($user);

        $this->userManager
            ->expects(self::never())
            ->method('createUser');

        $this->userManager
            ->expects(self::never())
            ->method('updateUser');

        $this->context->iAmAuthenticatedAsUser('me@example.com');

        self::assertSame(['ROLE_USER'], $user->getRoles());

        $this->assertSessionDataValid($user);
    }

    private function assertSessionDataValid(BaseUser $user): void
    {
        self::assertTrue($this->session->has('_security_foo'));
        self::assertSame($this->session->getId(), $this->mink->getSession()->getCookie($this->session->getName()));
        self::assertInstanceOf(UsernamePasswordToken::class, $this->tokenStorage->getToken());
        self::assertSame($user, $this->tokenStorage->getToken()->getUser());
    }
}
