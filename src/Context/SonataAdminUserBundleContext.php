<?php

/*
 * This file is part of the SonataAdminBehatContext package.
 *
 * (c) Oskar Stark <oskarstark@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OStark\Context;

use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Sonata\UserBundle\Entity\UserManager;
use Sonata\UserBundle\Model\UserManagerInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Defines features for the SonataAdmin context that make use of the Sonata UserBundle.
 */
final class SonataAdminUserBundleContext extends RawMinkContext implements KernelAwareContext
{
    const DEFAULT_USERNAME = 'test@example.com';

    /**
     * @var UserInterface
     */
    private $user;

    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    public function __construct(UserManager $userManager, TokenStorageInterface $tokenStorage, Session $session)
    {
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
    }

    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @When /^(?:|I )delete last created user$/
     *
     * @codeCoverageIgnore Selenium2Driver needed
     */
    public function deleteLastCreatedUser()
    {
        $user = $this->userManager->findBy([], ['createdAt' => 'DESC'], 1);
        $this->userManager->deleteUser(current($user));
    }

    /**
     * @Given /^I am an authenticated User$/
     *
     * @throws ExpectationException
     * @throws UnsupportedDriverActionException
     *
     * @codeCoverageIgnore Selenium2Driver needed
     */
    public function iAmAnAuthenticatedUser()
    {
        $user = $this->userManager->createUser();

        $user->setEmail(self::DEFAULT_USERNAME);
        $user->setUsername(self::DEFAULT_USERNAME);
        $user->setPlainPassword('foobar');

        $this->userManager->updateUser($user);

        $this->user = $user;

        $this->createUserSession($user);
    }

    /**
     * @Given /^I have role "([^"]*)"$/
     *
     * @param string $role
     *
     * @throws ExpectationException
     * @throws UnsupportedDriverActionException
     *
     * @codeCoverageIgnore Selenium2Driver needed
     */
    public function iHaveRole($role)
    {
        $user = $this->getCurrentUser();

        $user->setRoles([$role]);
        $this->userManager->updateUser($user);

        $this->user = $user;

        $this->createUserSession($user);
    }

    /**
     * @Given /^I am authenticated as User "([^"]*)"$/
     *
     * @param string $username
     *
     * @throws ExpectationException
     * @throws UnsupportedDriverActionException
     *
     * @codeCoverageIgnore Selenium2Driver needed
     */
    public function iAmAuthenticatedAsUser($username)
    {
        $driver = $this->getSession()->getDriver();

        $user = $this->userManager->findOneBy(['username' => $username]);
        if (null === $user) {
            throw new ExpectationException(sprintf('User with username "%s" does not exist', $username), $driver);
        }

        $this->user = $user;

        $this->createUserSession($user);
    }

    /**
     * @throws UnsupportedDriverActionException
     *
     * @codeCoverageIgnore Selenium2Driver needed
     */
    private function createUserSession(UserInterface $user)
    {
        $providerKey = $this->kernel->getContainer()->getParameter('fos_user.firewall_name');

        $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
        $this->tokenStorage->setToken($token);

        $authenticated = $this->tokenStorage->getToken()->isAuthenticated();
        if (!$authenticated) {
            throw new \RuntimeException('Not authenticated!');
        }

        $this->session->set('_security_'.$providerKey, serialize($token));
        $this->session->save();

        $driver = $this->getSession()->getDriver();
        if ($driver instanceof BrowserKitDriver) {
            $client = $driver->getClient();
            $cookie = new Cookie($this->session->getName(), $this->session->getId());
            $client->getCookieJar()->set($cookie);
        } elseif ($driver instanceof Selenium2Driver) {
            $this->visitPath('/'); // this step is needed, otherwise the user is not logged in the first time!
        } else {
            throw new UnsupportedDriverActionException('The Driver is not supported!', $driver);
        }

        $this->getSession()->setCookie($this->session->getName(), $this->session->getId());
    }

    private function getCurrentUser(): UserInterface
    {
        if (null != $this->user) {
            return $this->user;
        }

        $user = $this->userManager->findOneBy(['username' => self::DEFAULT_USERNAME]);
        if (null === $user) {
            throw new ExpectationException(sprintf('User with username "%s" does not exist', self::DEFAULT_USERNAME), $this->getSession()->getDriver());
        }

        return $user;
    }
}
