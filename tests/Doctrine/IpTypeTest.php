<?php

namespace Darsyn\IP\Tests\Doctrine;

use Darsyn\IP\Doctrine\IpType;
use Darsyn\IP\Tests\TestCase;
use Doctrine\DBAL\Types\Type;

class IpTypeTest extends TestCase
{
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
