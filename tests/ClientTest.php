<?php

declare(strict_types=1);

/*
 * This file is part of HelpScout Downloader.
 *
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\Tests\HelpScout;

use GrahamCampbell\HelpScout\Client;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * This is the client test class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class ClientTest extends TestCase
{
    public function testCanBeInstantiated()
    {
        $client = new Client('foo');

        $this->assertInstanceOf(Client::class, $client);
    }

    /**
     * @expectedException TypeError
     */
    public function testInstantiationRequiresParam()
    {
        new Client();
    }
}
