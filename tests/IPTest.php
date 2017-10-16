<?php

namespace Darsyn\IP\Tests\Doctrine;

use Darsyn\IP\IP;
use Darsyn\IP\Tests\TestCase;

class IPTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Skipping deprecated error test on HHVM.');
        }
    }

    /**
     * @test
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testDeprecatedIpEmitsUserError()
    {
        $ip = new IP('12.34.56.78');
    }
}
