<?php

namespace Darsyn\IP\Tests\Version;

use Darsyn\IP\Exception\InvalidCidrException;
use Darsyn\IP\Exception\InvalidIpAddressException;
use Darsyn\IP\IpInterface;
use Darsyn\IP\Tests\TestCase;
use Darsyn\IP\Version\IPv4 as IP;
use Darsyn\IP\Version\IPv6;
use Darsyn\IP\Version\Version4Interface;

class IPv4Test extends TestCase
{
    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testInstantiationWithValidAddresses($value)
    {
        $ip = new IP($value);
        $this->assertInstanceOf(IpInterface::class, $ip);
        $this->assertInstanceOf(Version4Interface::class, $ip);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidBinarySequences()
     */
    public function testBinarySequenceIsTheSameOnceInstantiated($value)
    {
        $ip = new IP($value);
        $this->assertSame($value, $ip->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    public function testProtocolNotationConvertsToCorrectBinarySequence($value, $expectedHex)
    {
        $ip = new IP($value);
        $this->assertSame($expectedHex, unpack('H*hex', $ip->getBinary())['hex']);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getInvalidIpAddresses()
     * @expectedException \Darsyn\IP\Exception\InvalidIpAddressException
     */
    public function testExceptionIsThrownOnInstantiationWithInvalidAddresses($value)
    {
        try {
            $ip = new IP($value);
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
        $ip = new IP($value);
        $this->assertSame(4, strlen(bin2hex($ip->getBinary())) / 2);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testDotAddressReturnsCorrectString($value, $expectedHex, $expectedDot)
    {
        $ip = new IP($value);
        $this->assertSame($expectedDot, $ip->getDotAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testGetVersionAlwaysReturns4($value)
    {
        $ip = new IP($value);
        $this->assertSame(4, $ip->getVersion());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testIsVersionOnlyReturnsTrueFor4($value)
    {
        $ip = new IP($value);
        $this->assertTrue($ip->isVersion(4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testIsVersionOnlyReturnsFalseFor6($value)
    {
        $ip = new IP($value);
        $this->assertFalse($ip->isVersion(6));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testIsVersion4AlwaysReturnsTrue($value)
    {
        $ip = new IP($value);
        $this->assertTrue($ip->isVersion4());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testIsVersion6AlwaysReturnsFalse($value)
    {
        $ip = new IP($value);
        $this->assertFalse($ip->isVersion6());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidCidrValues()
     */
    public function testCidrMasks($cidr, $expectedMaskHex)
    {
        $ip = new IP('12.34.56.78');
        $reflect = new \ReflectionClass($ip);
        $method = $reflect->getMethod('generateBinaryMask');
        $method->setAccessible(true);
        $this->assertSame($expectedMaskHex, unpack('H*hex', $method->invoke($ip, $cidr, 4))['hex']);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getInvalidCidrValues()
     * @expectedException \Darsyn\IP\Exception\InvalidCidrException
     */
    public function testExceptionIsThrownFromInvalidCidrValues($cidr)
    {
        $ip = new IP('12.34.56.78');
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
     */
    public function testNetworkIp()
    {
    }

    /**
     * @test
     */
    public function testBroadcastIp()
    {
    }

    /**
     * @test
     */
    public function testInRange()
    {
    }

    /**
     * @test
     */
    public function testDifferentVersionsAreNotInRange()
    {
        $ip = new IP('12.34.56.78');
        $other = new IPv6('::12.34.56.78');
        $this->assertFalse($ip->inRange($other, 0));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testIsMappedAlwaysReturnsFalse($value)
    {
        $ip = new IP($value);
        $this->assertFalse($ip->isMapped());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testIsDerivedAlwaysReturnsFalse($value)
    {
        $ip = new IP($value);
        $this->assertFalse($ip->isDerived());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testIsCompatibleAlwaysReturnsFalse($value)
    {
        $ip = new IP($value);
        $this->assertFalse($ip->isCompatible());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    public function testIsEmbeddedAlwaysReturnsFalse($value)
    {
        $ip = new IP($value);
        $this->assertFalse($ip->isEmbedded());
    }

    /**
     * @test
     */
    public function testIsLinkLocal()
    {
    }

    /**
     * @test
     */
    public function testIsLoopback()
    {
    }

    /**
     * @test
     */
    public function testIsMulticast()
    {
    }

    /**
     * @test
     */
    public function testIsPrivateUse()
    {
    }

    /**
     * @test
     */
    public function testIsUnspecified()
    {
    }
}
