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

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\MinkExtension\Context\MinkContext;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Exception;
use OStark\Exception\PackageNotFoundException;
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
final class SonataAdminContext extends RawMinkContext implements KernelAwareContext
{
    const DEFAULT_USERNAME = 'test@example.com';

    /**
     * @var UserInterface
     */
    private $user;

    /**
     * @var null|UserManagerInterface
     */
    protected $userManager;

    /**
     * @var null|TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var null|Session
     */
    protected $session;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var MinkContext
     */
    private $minkContext;

    public function __construct(?UserManagerInterface $userManager = null, ?TokenStorageInterface $tokenStorage = null, ?Session $session = null)
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
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->minkContext = $environment->getContext(MinkContext::class);
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
        $filterButton = $this->findElement(
            '//button[@class="btn btn-primary"]',
            'Filter-Button'
        );

        $filterButton->click();
    }

    /**
     * @When /^(?:|I )should see the filters$/
     *
     * @throws ElementNotFoundException
     */
    public function iShouldSeeTheFilters(): NodeElement
    {
        return $this->findElement(
            '//ul[contains(@class, "nav")]/li[contains(@class, "sonata-actions")]/a/i[contains(@class, "fa-filter")]/parent::a',
            'Filter'
        );
    }

    /**
     * @When /^(?:|I )should not see the filters$/
     *
     * @throws ExpectationException
     */
    public function iShouldNotSeeTheFilters()
    {
        $this->notFindElement(
            '//ul[contains(@class, "nav")]/li[contains(@class, "sonata-actions")]/a/i[contains(@class, "fa-filter")]/parent::a',
            'Filter found!'
        );
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
        $filter = $this->iShouldSeeTheFilters();

        $filter->click();
    }

    /**
     * @When /^(?:|I )select "([^"]*)" filter$/
     *
     * @param string $name
     *
     * @throws ElementNotFoundException
     *
     * @codeCoverageIgnore Selenium2Driver needed
     */
    public function iSelectFilter($name)
    {
        $element = $this->findElement(sprintf(
            '//ul[contains(@class, "nav")]/li[contains(@class, "sonata-actions")]/a/i[contains(@class, "fa-filter")]/parent::a/parent::li/ul/li/a[contains(., "%s")]',
            $name
        ), 'Filter');

        $element->click();
    }

    /**
     * Example: Then I select filters:
     *              | ID   |
     *              | Name |
     * Example: And I select filters:
     *              | ID   |
     *              | Name |.
     *
     * @When /^(?:|I )select filters:$/
     */
    public function iSelectFilters(TableNode $names)
    {
        foreach ($names->getRowsHash() as $name => $value) {
            $this->iSelectFilter($name);
        }
    }

    /**
     * @When /^(?:|I )should see "([^"]*)" filter$/
     *
     * @param string $filter
     *
     * @throws ElementNotFoundException
     */
    public function iShouldSeeFilter($filter)
    {
        $this->findElement(sprintf(
            '//div[contains(@class, "form-group")]//*[self::input or self::select][contains(@name, "filter[%s][value]")]',
            $this->fixFilter($filter)
        ), 'Filter');
    }

    /**
     * Example: Then I should see filters:
     *              | ID   |
     *              | Name |
     * Example: And I should see filters:
     *              | ID   |
     *              | Name |.
     *
     * @When /^(?:|I )should see filters:$/
     */
    public function iShouldSeeFilters(TableNode $names)
    {
        foreach ($names->getRowsHash() as $name => $value) {
            $this->iShouldSeeFilter($name);
        }
    }

    /**
     * @When /^(?:|I )filter "([^"]*)" with "([^"]*)"$/
     *
     * @param string $name
     * @param string $value
     *
     * @throws ElementNotFoundException
     *
     * @codeCoverageIgnore
     */
    public function iFilterWith($name, $value)
    {
        $this->iShouldSeeTheFilters();
        $this->iClickFilters();
        $this->iSelectFilter($name);
        $this->iShouldSeeFilter($name);
        $this->iClickFilters();
        $this->minkContext->fillField($name, $value);
        $this->iFilterTheList();
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
        $checkbox = $this->findElement(
            sprintf(
                '//table/tbody/tr[%s]/td[1]/div[@class="icheckbox_square-blue"]',
                $row
            ),
            sprintf('Checkbox in row %s', $row)
        );

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
        $allElementsCheckbox = $this->findElement(
            '//label[@class="checkbox"]',
            'All-Elements checkbox'
        );

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

        $flashMessage = $this->findElement(
            sprintf('//div[@class="alert alert-%s alert-dismissable"]', $status),
            'Flash-Message'
        );

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
        $xButton = $this->findElement(
            '//div[contains(@class, "alert-dismissable")]/button',
            'Flash-Message close button'
        );

        $xButton->click();
    }

    /**
     * @When /^(?:|I )delete last created user$/
     *
     * @codeCoverageIgnore Selenium2Driver needed
     */
    public function deleteLastCreatedUser()
    {
        $this->assertUserBundleDependencies();
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
        $this->assertUserBundleDependencies();
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
        $this->assertUserBundleDependencies();
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
        $this->assertUserBundleDependencies();
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
        $impersonateButton = $this->findElement(sprintf(
            '//table/tbody/tr[contains(., "%s")]/td[@data-name="impersonating"]//a[@title="Impersonate User"]',
            $user
        ), 'Impersonate User');

        $impersonateButton->click();
    }

    /**
     * @When /^(?:|I )select "([^"]*)" from batch actions$/
     *
     * @codeCoverageIgnore Selenium2Driver needed
     */
    public function iSelectFromBatchActions($action)
    {
        $element = $this->findElement(
            '//div[@class="box-footer"]//div[@class="select2-container"]',
            'Batch-Action'
        );
        $element->click();

        $action = $this->findElement(
            sprintf('//ul[@class="select2-results"]//div[text()="%s"]', $action),
            'Batch-Action'
        );
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
     * Example: Then I should see list columns:
     *              | ID   |
     *              | Name |
     * Example: And I should see list columns:
     *              | ID   |
     *              | Name |.
     *
     * @When /^(?:|I )should see list columns:$/
     */
    public function iShouldSeeListColumnsTable(TableNode $columns)
    {
        foreach ($columns->getRowsHash() as $column => $value) {
            $this->iShouldSeeListColumn($column);
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
     * Example: Then I should not see list columns:
     *              | ID   |
     *              | Name |
     * Example: And I should not see list columns:
     *              | ID   |
     *              | Name |.
     *
     * @When /^(?:|I )should not see list columns:$/
     */
    public function iShouldNotSeeListColumnsTable(TableNode $columns)
    {
        foreach ($columns->getRowsHash() as $column => $value) {
            $this->iShouldNotSeeListColumn($column);
        }
    }

    /**
     * @When /^(?:|I )should see "([^"]*)" list column$/
     *
     * @param string $name
     *
     * @throws ExpectationException
     * @throws ElementNotFoundException
     */
    public function iShouldSeeListColumn($name)
    {
        $this->findElement(
            sprintf('//table/thead//th[contains(., "%s")]', $name),
            'List-Column'
        );
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
            throw new ExpectationException('Column found, but should not!', $this->getSession()->getDriver());
        }
    }

    /**
     * @When /^(?:|I )should see "(?P<value>[^"]*)" in row "(?P<row>[^"]*)" on column "(?P<column>[^"]*)"$/
     * @When /^(?:|I )should see "(?P<value>[^"]*)" in row "(?P<row>[^"]*)" on column "(?P<column>[^"]*)" \(use data-name: "(?P<dataName>[^"]*)"\)$/
     * @When /^(?:|the )row "(?P<row>[^"]*)" should contain "(?P<value>[^"]*)" on column "(?P<column>[^"]*)"$/
     * @When /^(?:|the )row "(?P<row>[^"]*)" should contain "(?P<value>[^"]*)" on column "(?P<column>[^"]*)" \(use data-name: "(?P<dataName>[^"]*)"\)$/
     *
     * @param string      $value
     * @param string      $row
     * @param string      $column
     * @param string|null $dataName
     *
     * @throws ElementNotFoundException
     */
    public function iShouldSeeValueInRowOnColumn($value, $row, $column, $dataName = null)
    {
        $this->findElement(sprintf(
            '//table/tbody/tr[%s]/td[@data-name="%s" and normalize-space() = "%s"]',
            $row,
            is_null($dataName) ? $this->fixColumn($column) : $dataName,
            $value
        ), 'Value-In-Row');
    }

    /**
     * @When /^(?:|the )value in row "(?P<row>[^"]*)" on column "(?P<column>[^"]*)" should end with "(?P<end>[^"]*)"$/
     * @When /^(?:|the )value in row "(?P<row>[^"]*)" on column "(?P<column>[^"]*)" should end with "(?P<end>[^"]*)" \(use data-name: "(?P<dataName>[^"]*)"\)$/
     *
     * @param string      $end
     * @param string      $row
     * @param string      $column
     * @param string|null $dataName
     *
     * @throws ElementNotFoundException
     */
    public function valueInRowOnColumnShouldEndWith($end, $row, $column, $dataName = null)
    {
        $this->findElement(sprintf(
            '//table/tbody/tr[%s]/td[@data-name="%s" and substring(normalize-space(), string-length(normalize-space()) - string-length("%s") + 1) = "%s"]',
            $row,
            is_null($dataName) ? $this->fixColumn($column) : $dataName,
            $end,
            $end
        ), 'Value-In-Row should end with');
    }

    /**
     * @When /^(?:|I )should see nothing in row "(?P<row>[^"]*)" on column "(?P<column>[^"]*)"$/
     * @When /^(?:|I )should see nothing in row "(?P<row>[^"]*)" on column "(?P<column>[^"]*)" \(use data-name: "(?P<dataName>[^"]*)"\)$/
     * @When /^(?:|the )row "(?P<row>[^"]*)" should contain nothing on column "(?P<column>[^"]*)"$/
     * @When /^(?:|the )row "(?P<row>[^"]*)" should contain nothing on column "(?P<column>[^"]*)" \(use data-name: "(?P<dataName>[^"]*)"\)$/
     *
     * @param string      $row
     * @param string      $column
     * @param string|null $dataName
     *
     * @throws ElementNotFoundException
     */
    public function iShouldSeeNothingInRowOnColumn($row, $column, $dataName = null)
    {
        $value = '';

        $this->findElement(sprintf(
            '//table/tbody/tr[%s]/td[@data-name="%s" and normalize-space() = "%s"]',
            $row,
            is_null($dataName) ? $this->fixColumn($column) : $dataName,
            $value
        ), 'Nothing-In-Row');
    }

    /**
     * @When /^the "(?P<field>(?:[^"]|\\")*)" field should be emtpy$/
     * @When /^the field "(?P<field>(?:[^"]|\\")*)" should be emtpy$/
     */
    public function theFieldShouldBeEmtpy($field)
    {
        $field = $this->fixStepArgument($field);
        $this->assertSession()->fieldValueEquals($field, '');
    }

    /**
     * @When /^the "(?P<field>(?:[^"]|\\")*)" field should not be empty$/
     * @When /^the field "(?P<field>(?:[^"]|\\")*)" should not be empty$/
     */
    public function theFieldShouldNotBeEmtpy($field)
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
        $this->assertUserBundleDependencies();
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

    private function fixColumn(string $column): string
    {
        if (strstr($column, ' ')) {
            $parts = explode(' ', $column);

            $column = '';
            foreach ($parts as $key => $part) {
                if (0 == $key) {
                    $column .= mb_strtolower($part);
                } else {
                    $column .= ucfirst(mb_strtolower($part));
                }
            }
        } else {
            $column = mb_strtolower($column);
        }

        return $column;
    }

    private function fixFilter(string $filter): string
    {
        if (strstr($filter, ' ')) {
            $parts = explode(' ', $filter);

            $filter = '';
            foreach ($parts as $key => $part) {
                if (0 == $key) {
                    $filter .= mb_strtolower($part);
                } else {
                    $filter .= ucfirst(mb_strtolower($part));
                }
            }
        } else {
            $filter = mb_strtolower($filter);
        }

        return $filter;
    }

    /**
     * @param string $locator
     * @param string $type
     *
     * @return NodeElement
     *
     * @throws ElementNotFoundException
     */
    private function findElement(string $locator, string $type): NodeElement
    {
        $element = $this->getSession()->getPage()->find('xpath', $locator);

        if (!$element) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), $type, 'xpath', $locator);
        }

        return $element;
    }

    /**
     * @param string $locator
     * @param string $type
     *
     * @throws ExpectationException
     */
    private function notFindElement(string $locator, string $type): void
    {
        $element = $this->getSession()->getPage()->find('xpath', $locator);

        if ($element) {
            throw new ExpectationException(sprintf('%s found, but should not!', $type), $this->getSession()->getDriver());
        }
    }

    public static function getAcceptedSnippetType()
    {
        return 'regex';
    }

    private function assertUserBundleDependencies()
    {
        if ($this->userManager === null || $this->tokenStorage === null) {
            throw new PackageNotFoundException(
                sprintf('To use User & Authentication scenarios install %s package', 'sonata-project/user-bundle')
            );
        }
    }
}
