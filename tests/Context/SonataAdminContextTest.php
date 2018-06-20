<?php

namespace Tests\OStark\Context;

use OStark\Context\SonataAdminContext;
use OStark\Test\BaseTestCase;
use Sonata\UserBundle\Model\UserManagerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SonataAdminContextTest extends BaseTestCase
{
    /**
     * @var SonataAdminContext
     */
    private $context;

    protected function setUp()
    {
        $this->context = new SonataAdminContext(
            $this->createMock(UserManagerInterface::class),
            $this->createMock(TokenStorageInterface::class),
            $this->createMock(Session::class)
        );
    }

    /**
     * @test
     */
    public function iResetTheFilters()
    {
        $html = <<<EOF
<div><a href="#">Reset</a></div>
EOF;

        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->assertNull($this->context->iResetTheFilters());
    }

    /**
     * @test
     *
     * @expectedException \Behat\Mink\Exception\ExpectationException
     * @expectedExceptionMessage Link with id|title|alt|text "Reset" not found.
     */
    public function iResetTheFiltersInvalid()
    {
        $html = <<<EOF
<div></div>
EOF;

        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->context->iResetTheFilters();
    }

    /**
     * @test
     *
     * @expectedException \Behat\Mink\Exception\ExpectationException
     * @expectedExceptionMessage Could not find message "Foo" in Flash-Message!
     */
    public function iShouldSeeFlashMessageWithInvalidMessage()
    {
        $html = <<<EOF
<div class="alert alert-success alert-dismissable">
    <button aria-hidden="true" class="close" data-dismiss="alert" type="button">&times;</button> Bar
</div> 
EOF;

        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->context->iShouldSeeFlashMessageWith('success', 'Foo');
    }

    /**
     * @test
     *
     * @expectedException \Behat\Mink\Exception\ElementNotFoundException
     * @expectedExceptionMessage Flash-Message matching xpath "//div[@class="alert alert-success alert-dismissable"]" not found.
     */
    public function iShouldSeeFlashMessageWithInvalidStatus()
    {
        $html = <<<EOF
<div class="alert alert-warning alert-dismissable">
    <button aria-hidden="true" class="close" data-dismiss="alert" type="button">&times;</button> Bar
</div>
EOF;

        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->context->iShouldSeeFlashMessageWith('success', 'Foo');
    }

    /**
     * @test
     *
     * @dataProvider iShouldSeeFlashMessageWithProvider
     */
    public function iShouldSeeFlashMessageWith($html, $status, $message)
    {
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->assertNull($this->context->iShouldSeeFlashMessageWith($status, $message));
    }

    /**
     * @return array
     */
    public function iShouldSeeFlashMessageWithProvider()
    {
        return [
            [
                <<<EOF
<div class="alert alert-success alert-dismissable">
    <button aria-hidden="true" class="close" data-dismiss="alert" type="button">&times;</button> Successfully applied transition: Accept
</div>
EOF
                ,
                'success',
                'Successfully applied transition: Accept',
            ],
            [
                <<<EOF
<div class="alert alert-danger alert-dismissable">
    <button aria-hidden="true" class="close" data-dismiss="alert" type="button">&times;</button> Foo
</div>
EOF
                ,
                'danger',
                'Foo',
            ],
            [
                <<<EOF
<div class="alert alert-warning alert-dismissable">
    <button aria-hidden="true" class="close" data-dismiss="alert" type="button">&times;</button> Bar
</div>
EOF
                ,
                'warning',
                'Bar',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider iShouldSeeListColumnProvider
     */
    public function iShouldSeeListColumn($html, $name)
    {
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->assertNull($this->context->iShouldSeeListColumn($name));
    }

    public function iShouldSeeListColumnProvider()
    {
        return [
            [
                <<<EOF
<table>
<thead>
    <tr>
        <th>Test</th>
    </tr>
</thead>
</table>
EOF
                , 'Test',
            ],
            [
                <<<EOF
<table>
<thead>
    <tr>
        <th><div>Test1</div></th>
    </tr>
</thead>
</table>
EOF
                , 'Test1',
            ],
            [
                <<<EOF
<table>
<thead>
    <tr>
        <th><a>Test2</a></th>
    </tr>
</thead>
</table>
EOF
                , 'Test2',
            ],
        ];
    }

    /**
     * @test
     * @expectedExceptionMessage Column-Switcher matching xpath "//table/thead//th[contains(., "Test2")]" not found.
     * @expectedException \Behat\Mink\Exception\ElementNotFoundException
     */
    public function iShouldSeeListColumnElementNotFound()
    {
        $html = <<<EOF
<table>
<tbody>
    <tr>
        <td><a>Test2</a></td>
    </tr>
</tbody>
</table>
EOF;
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->context->iShouldSeeListColumn('Test2');
    }

    /**
     * @test
     */
    public function iShouldNotSeeListColumn()
    {
        $html = <<<EOF
<table>
<thead>
    <tr>
        <th><a>Test2</a></th>
    </tr>
</thead>
</table>
EOF;
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->assertNull($this->context->iShouldNotSeeListColumn('Foo'));
    }

    /**
     * @test
     * @expectedExceptionMessage Column was found!
     * @expectedException \Behat\Mink\Exception\ExpectationException
     */
    public function iShouldNotSeeListColumnElementFound()
    {
        $html = <<<EOF
<table>
<thead>
    <tr>
        <th><a>Foo</a></th>
    </tr>
</thead>
</table>
EOF;
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->context->iShouldNotSeeListColumn('Foo');
    }

    /**
     * @test
     */
    public function iShouldNotSeeTheFilters()
    {
        $html = <<<EOF
<ul>Foo</ul>
EOF;
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->assertNull($this->context->iShouldNotSeeTheFilters());
    }

    /**
     * @test
     */
    public function iShouldSeeTheFilters()
    {
        $html = <<<EOF
<ul class="nav navbar-nav navbar-right">
    <li class="dropdown sonata-actions">
        <a href="#" class="dropdown-toggle sonata-ba-action" data-toggle="dropdown">
            <i class="fa fa-filter" aria-hidden="true"></i> Filters <b class="caret"></b>
        </a>
    </li>
</ul>
EOF;
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->assertNull($this->context->iShouldSeeTheFilters());
    }

    /**
     * @test
     *
     * @expectedExceptionMessage Filter matching xpath "//ul[contains(@class, "nav")]/li[contains(@class, "sonata-actions")]/a/i[contains(@class, "fa-filter")]/parent::a" not found.
     * @expectedException \Behat\Mink\Exception\ElementNotFoundException
     */
    public function iShouldSeeTheFiltersNotFound()
    {
        $html = <<<EOF
<ul>Foo</ul>
EOF;
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->context->iShouldSeeTheFilters();
    }

    /**
     * @test
     * @expectedExceptionMessage Filter found!
     * @expectedException \Behat\Mink\Exception\ExpectationException
     */
    public function iShouldNotSeeTheFiltersElementFound()
    {
        $html = <<<EOF
<ul class="nav navbar-nav navbar-right">
    <li class="dropdown sonata-actions">
        <a href="#" class="dropdown-toggle sonata-ba-action" data-toggle="dropdown">
            <i class="fa fa-filter" aria-hidden="true"></i> Filters <b class="caret"></b>
        </a>
    </li>
</ul>
EOF;
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->context->iShouldNotSeeTheFilters();
    }

    /**
     * @test
     *
     * @expectedExceptionMessage Filter matching xpath "//div[contains(@class, "form-group")]//input[contains(@name, "filter[fooBar][value]")]" not found.
     * @expectedException \Behat\Mink\Exception\ElementNotFoundException
     */
    public function iShouldSeeFilterNotFound()
    {
        $html = <<<EOF
<div class="form-group">
    <input name="test">
</div>
EOF;
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->context->iShouldSeeFilter('Foo Bar');
    }

    /**
     * @test
     * @dataProvider iShouldSeeFilterProvider
     */
    public function iShouldSeeFilter($html, $name)
    {
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->assertNull($this->context->iShouldSeeFilter($name));
    }

    public function iShouldSeeFilterProvider()
    {
        return [
            [
                <<<EOF
<div class="form-group">
    <input name="filter[foo][value]">
</div>
EOF
                , 'Foo',
            ],
            [
                <<<EOF
<div class="form-group">
    <input name="filter[fooTestBar][value]">
</div>
EOF
                , 'Foo Test Bar',
            ],
            [
                <<<EOF
<div class="form-group">
    <input name="filter[fooBar][value]">
</div>
EOF
                , 'Foo Bar',
            ],
        ];
    }

    /**
     * @test
     *
     * @expectedExceptionMessage $columns must be separated by a colon!
     * @expectedException \InvalidArgumentException
     */
    public function iShouldNotSeeListColumnsWithInvalidArgument()
    {
        $html = <<<EOF
<div></div>
EOF;
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->context->iShouldNotSeeListColumns('Foo Bar');
    }

    /**
     * @test
     */
    public function iShouldNotSeeListColumns()
    {
        $html = <<<EOF
<table>
<thead>
    <tr>
        <th><a>Test2</a></th>
    </tr>
</thead>
</table>
EOF;
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->assertNull($this->context->iShouldNotSeeListColumns('Bar, Foo'));
    }

    /**
     * @test
     *
     * @expectedExceptionMessage $columns must be separated by a colon!
     * @expectedException \InvalidArgumentException
     */
    public function iShouldSeeListColumnsWithInvalidArgument()
    {
        $html = <<<EOF
<div></div>
EOF;
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->context->iShouldSeeListColumns('Foo Bar');
    }

    /**
     * @test
     *
     * @dataProvider iShouldSeeListColumnsProvider
     */
    public function iShouldSeeListColumns($html, $name)
    {
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->assertNull($this->context->iShouldSeeListColumns($name));
    }

    public function iShouldSeeListColumnsProvider()
    {
        return [
            [
                <<<EOF
<table>
<thead>
    <tr>
        <th>Test</th>
        <th>Foo</th>
    </tr>
</thead>
</table>
EOF
                , 'Test,Foo',
            ],
            [
                <<<EOF
<table>
<thead>
    <tr>
        <th><div>Test1</div></th>
        <th><div>Foo</div></th>
    </tr>
</thead>
</table>
EOF
                , 'Test1, Foo',
            ],
            [
                <<<EOF
<table>
<thead>
    <tr>
        <th><a>Test2</a></th>
        <th><a>Foo</a></th>
    </tr>
</thead>
</table>
EOF
                , 'Test2 , Foo',
            ],
        ];
    }

    /**
     * @test
     */
    public function theFieldShouldBeEmtpy()
    {
        $html = <<<EOF
<form><input type="text" name="fooBar" value=""></form>
EOF;
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->assertNull($this->context->theFieldShouldBeEmtpy('fooBar'));
    }

    /**
     * @test
     * @expectedException \Behat\Mink\Exception\ExpectationException
     * @expectedExceptionMessage The field "fooBar" value is "Foo Bar", but "" expected.
     */
    public function theFieldShouldBeEmtpyButItsNot()
    {
        $html = <<<EOF
        <form><input type="text" name="fooBar" value="Foo Bar"></form>
EOF;
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->context->theFieldShouldBeEmtpy('fooBar');
    }

    /**
     * @test
     */
    public function theFieldShouldNotBeEmtpy()
    {
        $html = <<<EOF
<form><input type="text" name="blaa" value="test value blaaaa"></form>
EOF;
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->assertNull($this->context->theFieldShouldNotBeEmtpy('blaa'));
    }

    /**
     * @test
     * @expectedException \Behat\Mink\Exception\ExpectationException
     * @expectedExceptionMessage The field "blaa" value is "", but it should not be.
     */
    public function theFieldShouldNotBeEmtpyButItIs()
    {
        $html = <<<EOF
        <form><input type="text" name="blaa" value=""></form>
EOF;
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->context->theFieldShouldNotBeEmtpy('blaa');
    }

    /**
     * @test
     */
    public function iShouldSeeValueInRowOnColumn()
    {
        $html = <<<EOF
<table>
<tbody>
    <tr><td data-name="bar">tralala</td><td data-name="foo"></td></tr>
    <tr></tr>
</tbody>
</table>
EOF;
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->assertNull($this->context->iShouldSeeValueInRowOnColumn('tralala', 1, 'bar'));
    }

    /**
     * @test
     * @expectedException \Behat\Mink\Exception\ElementNotFoundException
     * @expectedExceptionMessage Value-In-Row matching xpath "//table/tbody/tr[1]/td[@data-name="bar"]" not found.
     */
    public function iShouldSeeValueInRowOnColumnElementNotFound()
    {
        $html = <<<EOF
<table>
<thead>
    <tr><td data-name="bar">tralala</td><td data-name="foo"></td></tr>
    <tr></tr>
</thead>
</table>
EOF;
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->context->iShouldSeeValueInRowOnColumn('tralala', 1, 'bar');
    }

    /**
     * @test
     * @expectedException \Behat\Mink\Exception\ExpectationException
     * @expectedExceptionMessage Could not find value!
     */
    public function iShouldSeeValueInRowOnColumnValueNotFound()
    {
        $html = <<<EOF
<table>
<tbody>
    <tr><td data-name="bar">test foo</td><td data-name="foo"></td></tr>
    <tr></tr>
</tbody>
</table>
EOF;
        $mink = self::setupMink($html);

        $this->context->setMink($mink);
        $this->context->iShouldSeeValueInRowOnColumn('tralala', 1, 'bar');
    }
}
