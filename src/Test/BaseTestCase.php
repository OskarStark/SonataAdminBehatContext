<?php

namespace OStark\Test;

use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Mink;
use Behat\Mink\Session;
use PHPUnit\Framework\TestCase;
use Symfony\Component\BrowserKit\Response;

class BaseTestCase extends TestCase
{
    /**
     * @param $html
     *
     * @return Mink
     *
     * @throws \Behat\Mink\Exception\DriverException
     * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
     */
    protected static function setupMink($html)
    {
        $client = new TestClient();
        $client->setNextResponse(new Response($html));
        $driver = new BrowserKitDriver($client);
        $driver->visit('/');

        $session = new Session($driver);
        $mink = new Mink(['default' => $session]);
        $mink->setDefaultSessionName('default');

        return $mink;
    }
}
