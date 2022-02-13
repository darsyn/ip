<?php

namespace Darsyn\IP\Tests\Version;

use Darsyn\IP\Exception\InvalidCidrException;
use Darsyn\IP\Exception\InvalidIpAddressException;
use Darsyn\IP\Exception\WrongVersionException;
use Darsyn\IP\IpInterface;
use Darsyn\IP\Version\IPv4 as IP;
use Darsyn\IP\Version\IPv6;
use Darsyn\IP\Version\Version4Interface;
use PHPUnit\Framework\TestCase;

class IPv4Test extends TestCase
{
    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testInstantiationWithValidAddresses($value)
    {
        $ip = IP::factory($value);
        $this->assertInstanceOf(IpInterface::class, $ip);
        $this->assertInstanceOf(Version4Interface::class, $ip);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidBinarySequences()
     */
    public function testBinarySequenceIsTheSameOnceInstantiated($value)
    {
        $ip = IP::factory($value);
        $this->assertSame($value, $ip->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    public function testProtocolNotationConvertsToCorrectBinarySequence($value, $expectedHex)
    {
        $ip = IP::factory($value);
        $this->assertSame($expectedHex, unpack('H*hex', $ip->getBinary())['hex']);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getInvalidIpAddresses()
     */
    public function testExceptionIsThrownOnInstantiationWithInvalidAddresses($value)
    {
        $this->expectException(\Darsyn\IP\Exception\InvalidIpAddressException::class);
        $this->expectExceptionMessage('The IP address supplied is not valid.');
        try {
            IP::factory($value);
        } catch (InvalidIpAddressException $e) {
            $this->assertSame($value, $e->getSuppliedIp());
            throw $e;
        }
        $this->fail();
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testGetBinaryAlwaysReturnsA4ByteString($value)
    {
        $ip = IP::factory($value);
        $this->assertSame(4, strlen(bin2hex($ip->getBinary())) / 2);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testDotAddressReturnsCorrectString($value, $expectedHex, $expectedDot)
    {
        $ip = IP::factory($value);
        $this->assertSame($expectedDot, $ip->getDotAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testGetVersionAlwaysReturns4($value)
    {
        $ip = IP::factory($value);
        $this->assertSame(4, $ip->getVersion());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testIsVersionOnlyReturnsTrueFor4($value)
    {
        $ip = IP::factory($value);
        $this->assertTrue($ip->isVersion(4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testIsVersionOnlyReturnsFalseFor6($value)
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isVersion(6));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testIsVersion4AlwaysReturnsTrue($value)
    {
        $ip = IP::factory($value);
        $this->assertTrue($ip->isVersion4());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testIsVersion6AlwaysReturnsFalse($value)
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isVersion6());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidCidrValues()
     */
    public function testCidrMasks($cidr, $expectedMaskHex)
    {
        $ip = IP::factory('12.34.56.78');
        $reflect = new \ReflectionClass($ip);
        $method = $reflect->getMethod('generateBinaryMask');
        $method->setAccessible(true);
        $this->assertSame($expectedMaskHex, unpack('H*hex', $method->invoke($ip, $cidr, 4))['hex']);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getInvalidCidrValues()
     */
    public function testExceptionIsThrownFromInvalidCidrValues($cidr)
    {
        $this->expectException(\Darsyn\IP\Exception\InvalidCidrException::class);
        $this->expectExceptionMessage('The CIDR supplied is not valid; it must be an integer between 0 and 32.');
        $ip = IP::factory('12.34.56.78');
        $reflect = new \ReflectionClass($ip);
        $method = $reflect->getMethod('generateBinaryMask');
        $method->setAccessible(true);
        try {
            $method->invoke($ip, $cidr, 4);
        } catch (InvalidCidrException $e) {
            $this->assertSame($cidr, $e->getSuppliedCidr());
            throw $e;
        }
        $this->fail();
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Ipv4::getNetworkIpAddresses()
     */
    public function testNetworkIp($expected, $cidr)
    {
        $ip = IP::factory('12.34.56.78');
        $this->assertSame($expected, $ip->getNetworkIp($cidr)->getDotAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Ipv4::getBroadcastIpAddresses()
     */
    public function testBroadcastIp($expected, $cidr)
    {
        $ip = IP::factory('12.34.56.78');
        $this->assertSame($expected, $ip->getBroadcastIp($cidr)->getDotAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidInRangeIpAddresses()
     */
    public function testInRange($first, $second, $cidr)
    {
        $first = IP::factory($first);
        $second = IP::factory($second);
        $this->assertTrue($first->inRange($second, $cidr));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getInvalidCidrValues()
     */
    public function testInRangeReturnsFalseInsteadOfExceptionOnInvalidCidr($cidr)
    {
        $first = IP::factory('12.34.56.78');
        $second = IP::factory('12.34.56.78');
        $this->expectException(InvalidCidrException::class);
        $first->inRange($second, $cidr);
    }

    /**
     * @test
     */
    public function testDifferentVersionsAreNotInRange()
    {
        $ip = IP::factory('12.34.56.78');
        $other = IPv6::factory('::12.34.56.78');
        $this->expectException(WrongVersionException::class);
        $ip->inRange($other, 0);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testIsMappedAlwaysReturnsFalse($value)
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isMapped());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testIsDerivedAlwaysReturnsFalse($value)
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isDerived());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testIsCompatibleAlwaysReturnsFalse($value)
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isCompatible());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testIsEmbeddedAlwaysReturnsFalse($value)
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isEmbedded());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getLinkLocalIpAddresses()
     */
    public function testIsLinkLocal($value, $isLinkLocal)
    {
        $ip = IP::factory($value);
        $this->assertSame($isLinkLocal, $ip->isLinkLocal());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getLoopbackIpAddresses()
     */
    public function testIsLoopback($value, $isLoopback)
    {
        $ip = IP::factory($value);
        $this->assertSame($isLoopback, $ip->isLoopback());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getMulticastIpAddresses()
     */
    public function testIsMulticast($value, $isMulticast)
    {
        $ip = IP::factory($value);
        $this->assertSame($isMulticast, $ip->isMulticast());

    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getPrivateUseIpAddresses()
     */
    public function testIsPrivateUse($value, $isPrivateUse)
    {
        $ip = IP::factory($value);
        $this->assertSame($isPrivateUse, $ip->isPrivateUse());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getUnspecifiedIpAddresses()
     */
    public function testIsUnspecified($value, $isUnspecified)
    {
        $ip = IP::factory($value);
        $this->assertSame($isUnspecified, $ip->isUnspecified());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testStringCasting($value, $expectedHex, $expectedDot)
    {
        $ip = IP::factory($value);
        $this->assertSame($expectedDot, (string) $ip);
    }
}
