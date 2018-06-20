<?php

namespace OStark\Context;

use Behat\Behat\Context\CustomSnippetAcceptingContext;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Sonata\UserBundle\Model\UserManagerInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Defines features for the SonataAdmin context.
 */
final class SonataAdminContext extends RawMinkContext implements CustomSnippetAcceptingContext, KernelAwareContext
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

    public function __construct(UserManagerInterface $userManager, TokenStorageInterface $tokenStorage, Session $session)
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
     * @When /^(?:|I )reset the filters$/
     */
    public function iResetTheFilters()
    {
        $this->getSession()->getPage()->clickLink('Reset');
    }

    /**
     * @When /^(?:|I )filter the list$/
     *
     * @throws ElementNotFoundException
     *
     * @codeCoverageIgnore Selenium2Driver needed
     */
    public function iFilterTheList()
    {
        $session = $this->getSession();

        $locator = '//button[@class="btn btn-primary"]';
        $filterButton = $session->getPage()->find('xpath', $locator);

        if (null === $filterButton) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), 'Filter-Button', 'xpath', $locator);
        }

        $filterButton->click();
    }

    /**
     * @When /^(?:|I )should see the filters$/
     *
     * @throws ElementNotFoundException
     */
    public function iShouldSeeTheFilters()
    {
        $session = $this->getSession();
        $locator = '//ul[contains(@class, "nav")]/li[contains(@class, "sonata-actions")]/a/i[contains(@class, "fa-filter")]/parent::a';
        $filter = $session->getPage()->find('xpath', $locator);

        if (!$filter) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), 'Filter', 'xpath', $locator);
        }
    }

    /**
     * @When /^(?:|I )should not see the filters$/
     *
     * @throws ExpectationException
     */
    public function iShouldNotSeeTheFilters()
    {
        $session = $this->getSession();
        $filter = $session->getPage()->find('xpath', '//ul[contains(@class, "nav")]/li[contains(@class, "sonata-actions")]/a/i[contains(@class, "fa-filter")]/parent::a');

        if ($filter) {
            throw new ExpectationException('Filter found!', $this->getSession()->getDriver());
        }
    }

    /**
     * @When /^(?:|I )click filters$/
     *
     * @throws ElementNotFoundException
     *
     * @codeCoverageIgnore Selenium2Driver needed
     */
    public function iClickFilters()
    {
        $session = $this->getSession();
        $locator = '//ul[contains(@class, "nav")]/li[contains(@class, "sonata-actions")]/a/i[contains(@class, "fa-filter")]/parent::a';
        $filter = $session->getPage()->find('xpath', $locator);

        if (!$filter) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), 'Filter', 'xpath', $locator);
        }

        $filter->click();
    }

    /**
     * @When /^(?:|I )select "([^"]*)" filter$/
     *
     * @param string $element
     *
     * @throws ElementNotFoundException
     *
     * @codeCoverageIgnore Selenium2Driver needed
     */
    public function iSelectElementFilter($element)
    {
        $session = $this->getSession();
        $locator = sprintf(
            '//ul[contains(@class, "nav")]/li[contains(@class, "sonata-actions")]/a/i[contains(@class, "fa-filter")]/parent::a/parent::li/ul/li/a[contains(., "%s")]',
            $element
        );

        $element = $session->getPage()->find(
            'xpath',
            $locator
        );

        if (!$element) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), 'Filter', 'xpath', $locator);
        }

        $element->click();
    }

    /**
     * @When /^(?:|I )should see "([^"]*)" filter$/
     *
     * @param string $name
     *
     * @throws ElementNotFoundException
     */
    public function iShouldSeeFilter($name)
    {
        $session = $this->getSession();
        $name = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $name))));
        $locator = sprintf('//div[contains(@class, "form-group")]//input[contains(@name, "filter[%s][value]")]', $name);

        $element = $session->getPage()->find(
            'xpath',
            $locator
        );

        if (!$element) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), 'Filter', 'xpath', $locator);
        }
    }

    /**
     * @When /^(?:|I )check checkbox in row "([^"]*)"$/
     *
     * @param int $row
     *
     * @throws ElementNotFoundException
     *
     * @codeCoverageIgnore Selenium2Driver needed
     */
    public function iCheckCheckboxInRow($row)
    {
        $session = $this->getSession();

        $locator = sprintf(
            '//table/tbody/tr[%s]/td[1]/div[@class="icheckbox_square-blue"]',
            $row
        );

        $checkbox = $session->getPage()->find('xpath', $locator);

        if (!$checkbox) {
            throw new ElementNotFoundException(
                $this->getSession()->getDriver(),
                sprintf('Checkbox in row "%s"', $row),
                'xpath',
                $locator
            );
        }

        $checkbox->click();
    }

    /**
     * @When /^(?:|I )check All-Elements checkbox$/
     *
     * @throws ElementNotFoundException
     *
     * @codeCoverageIgnore Selenium2Driver needed
     */
    public function iCheckAllElementsCheckbox()
    {
        $session = $this->getSession();

        $locator = '//label[@class="checkbox"]';
        $allElementsCheckbox = $session->getPage()->find('xpath', $locator);

        if (null === $allElementsCheckbox) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), 'All-Elements checkbox', 'xpath', $locator);
        }

        $allElementsCheckbox->click();
    }

    /**
     * @When /^(?:|I )should see "([^"]*)" flash message with "(?P<text>(?:[^"]|\\")*)"$/
     *
     * @param string $status
     * @param string $message
     *
     * @throws ElementNotFoundException
     * @throws ExpectationException
     */
    public function iShouldSeeFlashMessageWith($status, $message)
    {
        $message = $this->fixStepArgument($message);
        $session = $this->getSession();

        $locator = sprintf('//div[@class="alert alert-%s alert-dismissable"]', $status);
        $flashMessage = $session->getPage()->find('xpath', $locator);

        if (null === $flashMessage) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), 'Flash-Message', 'xpath', $locator);
        }

        $text = $flashMessage->getText();
        if (!strstr($text, $message)) {
            throw new ExpectationException(
                sprintf('Could not find message "%s" in Flash-Message!', $message),
                $this->getSession()->getDriver()
            );
        }
    }

    /**
     * @When /^(?:|I )close flash message$/
     *
     * @throws ElementNotFoundException
     *
     * @codeCoverageIgnore Selenium2Driver needed
     */
    public function iCloseFlashMessage()
    {
        $session = $this->getSession();
        $locator = '//div[contains(@class, "alert-dismissable")]/button';
        $xButton = $session->getPage()->find('xpath', $locator);

        if (null === $xButton) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), 'Flash-Message close button', 'xpath', $locator);
        }

        $xButton->click();
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
            throw new ExpectationException(
                sprintf('User with username "%s" does not exist', $username),
                $driver
            );
        }

        $this->user = $user;

        $this->createUserSession($user);
    }

    /**
     * @When /^(?:|I )logout User$/
     *
     * @codeCoverageIgnore No need to test session
     */
    public function iLogoutUser()
    {
        $this->getSession()->getPage()->clickLink('Sign out');
    }

    /**
     * @When /^(?:|I )impersonate user "([^"]*)"$/
     *
     * @param $user
     *
     * @throws ElementNotFoundException
     *
     * @codeCoverageIgnore Selenium2Driver needed
     */
    public function iImpersonateUser($user)
    {
        $session = $this->getSession();

        $locator = sprintf(
            '//table/tbody/tr[contains(., "%s")]/td[@data-name="impersonating"]//a[@title="Impersonate User"]',
            $user
        );

        $element = $session->getPage()->find('xpath', $locator);

        if (!$element) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), 'Impersonate User '.$user, 'xpath', $locator);
        }

        $element->click();
    }

    /**
     * @When /^(?:|I )select "([^"]*)" from batch actions$/
     *
     * @codeCoverageIgnore Selenium2Driver needed
     */
    public function iSelectFromBatchActions($action)
    {
        $session = $this->getSession();
        $element = $session->getPage()->find(
            'xpath',
            '//div[@class="box-footer"]//div[@class="select2-container"]'
        );

        $element->click();

        $action = $session->getPage()->find('xpath', '//ul[@class="select2-results"]//div[text()="'.$action.'"]');
        $action->click();
    }

    /**
     * @When /^(?:|I )should see "([^"]*)" list columns$/
     *
     * @param string $columns
     *
     * @throws ElementNotFoundException
     */
    public function iShouldSeeListColumns($columns)
    {
        if (!strstr($columns, ',')) {
            throw new \InvalidArgumentException('$columns must be separated by a colon!');
        }

        foreach (explode(',', $columns) as $column) {
            $this->iShouldSeeListColumn(trim($column));
        }
    }

    /**
     * @When /^(?:|I )should not see "([^"]*)" list columns$/
     *
     * @param string $columns
     *
     * @throws ExpectationException
     */
    public function iShouldNotSeeListColumns($columns)
    {
        if (!strstr($columns, ',')) {
            throw new \InvalidArgumentException('$columns must be separated by a colon!');
        }

        foreach (explode(',', $columns) as $column) {
            $this->iShouldNotSeeListColumn(trim($column));
        }
    }

    /**
     * @When /^(?:|I )should see "([^"]*)" list column$/
     *
     * @param string $name
     *
     * @throws ElementNotFoundException
     */
    public function iShouldSeeListColumn($name)
    {
        $session = $this->getSession();
        $locator = sprintf('//table/thead//th[contains(., "%s")]', $name);

        $element = $session->getPage()->find(
            'xpath',
            $locator
        );

        if (!$element) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), 'Column', 'xpath', $locator);
        }
    }

    /**
     * @When /^(?:|I )should not see "([^"]*)" list column$/
     *
     * @param string $name
     *
     * @throws ExpectationException
     */
    public function iShouldNotSeeListColumn($name)
    {
        $session = $this->getSession();
        $locator = sprintf('//table/thead//th[contains(., "%s")]', $name);

        $element = $session->getPage()->find(
            'xpath',
            $locator
        );

        if ($element && 'display: none;' == !$element->getAttribute('style')) {
            throw new ExpectationException('Column was found!', $this->getSession()->getDriver());
        }
    }

    /**
     * @When /^(?:|I )should see "([^"]*)" in row "([^"]*)" on column "([^"]*)"$/
     *
     * @param string $value
     * @param string $row
     * @param string $column
     *
     * @throws ElementNotFoundException
     * @throws ExpectationException
     */
    public function iShouldSeeValueInRowOnColumn($value, $row, $column)
    {
        $session = $this->getSession();
        $column = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $column))));
        $locator = sprintf('//table/tbody/tr[%s]/td[@data-name="%s"]', $row, $column);

        $element = $session->getPage()->find(
            'xpath',
            $locator
        );

        if (!$element) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), 'Value-In-Row', 'xpath', $locator);
        }

        if (!strstr($element->getHtml(), $value)) {
            throw new ExpectationException('Could not find value!', $this->getSession()->getDriver());
        }
    }

    /**
     * @When /^the "(?P<field>(?:[^"]|\\")*)" field should be empty$/
     */
    public function assertFieldEmpty($field)
    {
        $field = $this->fixStepArgument($field);
        $this->assertSession()->fieldValueEquals($field, '');
    }

    /**
     * @When /^the "(?P<field>(?:[^"]|\\")*)" field should not be empty$/
     */
    public function assertFieldNotEmpty($field)
    {
        $field = $this->fixStepArgument($field);
        $this->assertSession()->fieldValueNotEquals($field, '');
    }

    /**
     * @param UserInterface $user
     *
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
            throw new ExpectationException(
                sprintf('User with username "%s" does not exist', self::DEFAULT_USERNAME),
                $this->getSession()->getDriver()
            );
        }

        return $user;
    }

    /**
     * Returns fixed step argument (with \\" replaced back to ").
     *
     * @param string $argument
     *
     * @return string
     */
    private function fixStepArgument($argument)
    {
        return str_replace('\\"', '"', $argument);
    }

    public static function getAcceptedSnippetType()
    {
        return 'regex';
    }
}
