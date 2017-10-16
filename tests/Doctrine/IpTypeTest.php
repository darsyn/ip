<?php

namespace Darsyn\IP\Tests\Doctrine;

use Darsyn\IP\Doctrine\IpType;
use Darsyn\IP\Tests\TestCase;
use Doctrine\DBAL\Types\Type;

class IpTypeTest extends TestCase
{
    protected function setUp()
    {
        if (PHP_INT_SIZE == 4) {
            $this->markTestSkipped('Skipping test that can run only on a 64-bit build of PHP.');
        }
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
