<?php

namespace Darsyn\IP\Tests\Doctrine;

use Darsyn\IP\Doctrine\IpType;
use Darsyn\IP\Tests\TestCase;
use Doctrine\DBAL\Types\Type;

class IpTypeTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        if (!class_exists('Doctrine\DBAL\Types\Type')) {
            $this->markTestSkipped('Skipping test that requires "doctrine/dbal".');
        }
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Skipping deprecated error test on HHVM.');
        }
    }

    /**
     * @test
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testIpTypeEmitsUserDeprecatedError()
    {
        Type::addType('deprecated_ip', IpType::class);
        $deprecatedIpType = Type::getType('deprecated_ip');
    }
}
