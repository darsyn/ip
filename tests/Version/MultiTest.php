<?php

namespace Darsyn\IP\Tests\Version;

use Darsyn\IP\Exception\InvalidIpAddressException;
use Darsyn\IP\Exception\WrongVersionException;
use Darsyn\IP\IpInterface;
use Darsyn\IP\Strategy;
use Darsyn\IP\Version\IPv4;
use Darsyn\IP\Version\IPv6;
use Darsyn\IP\Version\Multi as IP;
use Darsyn\IP\Version\MultiVersionInterface;
use Darsyn\IP\Version\Version4Interface;
use Darsyn\IP\Version\Version6Interface;
use PHPUnit\Framework\TestCase;

class MultiTest extends TestCase
{
    /** @before */
    public function resetDefaultEmbeddingStrategy()
    {
        IP::setDefaultEmbeddingStrategy(new Strategy\Mapped);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidIpAddresses()
     */
    public function testInstantiationWithValidAddresses($value)
    {
        $ip = IP::factory($value);
        $this->assertInstanceOf(IpInterface::class, $ip);
        $this->assertInstanceOf(Version4Interface::class, $ip);
        $this->assertInstanceOf(Version6Interface::class, $ip);
        $this->assertInstanceOf(MultiVersionInterface::class, $ip);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getEmbeddingStrategyIpAddresses()
     */
    public function testEmbeddingStrategy($strategyClass, $expandedAddress, $v4address)
    {
        $ip = IP::factory($v4address, new $strategyClass);
        $this->assertSame($expandedAddress, $ip->getExpandedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getEmbeddingStrategyIpAddresses()
     */
    public function testDefaufltEmbeddingStrategy($strategyClass, $expandedAddress, $v4address)
    {
        IP::setDefaultEmbeddingStrategy(new $strategyClass);
        $ip = IP::factory($v4address);
        $this->assertSame($expandedAddress, $ip->getExpandedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidBinarySequences()
     */
    public function testBinarySequenceIsTheSameOnceInstantiated($value)
    {
        $ip = IP::factory($value);
        $this->assertSame($value, $ip->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpAddresses()
     */
    public function testProtocolNotationConvertsToCorrectBinarySequence($value, $hex)
    {
        $ip = IP::factory($value);
        $this->assertSame($hex, unpack('H*hex', $ip->getBinary())['hex']);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getInvalidIpAddresses()
     */
    public function testExceptionIsThrownOnInstantiationWithInvalidAddresses($value)
    {
        $this->expectException(InvalidIpAddressException::class);
        $this->expectExceptionMessage('The IP address supplied is not valid.');
        try {
            $ip = IP::factory($value);
        } catch (InvalidIpAddressException $e) {
            $this->assertSame($value, $e->getSuppliedIp());
            throw $e;
        }
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidIpAddresses()
     */
    public function testGetBinaryAlwaysReturnsA16ByteString($value)
    {
        $ip = IP::factory($value);
        $this->assertSame(16, strlen(bin2hex($ip->getBinary())) / 2);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidIpAddresses()
     */
    public function testGetCompactedAddressReturnsCorrectString($value, $hex, $expanded, $compacted)
    {
        $ip = IP::factory($value);
        $this->assertSame($compacted, $ip->getCompactedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpAddresses()
     */
    public function testGetExpandedAddressReturnsCorrectString($value, $hex, $expanded)
    {
        $ip = IP::factory($value);
        $this->assertSame($expanded, $ip->getExpandedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidIpVersion4Addresses()
     */
    public function testDotAddressReturnsCorrectString($value, $hex, $expanded, $compacted, $dot)
    {
        $ip = IP::factory($value);
        $this->assertSame($dot, $ip->getDotAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidIpVersion6Addresses()
     */
    public function testDotAddressThrowsExceptionForNonVersion4Addresses($value)
    {
        $this->expectException(\Darsyn\IP\Exception\WrongVersionException::class);
        try {
            $ip = IP::factory($value);
            $ip->getDotAddress();
        } catch (WrongVersionException $e) {
            $this->assertSame((string) $ip, $e->getSuppliedIp());
            $this->assertSame(4, $e->getExpectedVersion());
            $this->assertSame(6, $e->getActualVersion());
            throw $e;
        }
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getIpAddressVersions()
     */
    public function testVersion($value, $version)
    {
        $ip = IP::factory($value);
        $this->assertSame($version, $ip->getVersion());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getNetworkIpAddresses()
     */
    public function testNetworkIp($initial, $expected, $cidr)
    {
        $ip = IP::factory($initial);
        $this->assertSame($expected, $ip->getNetworkIp($cidr)->getProtocolAppropriateAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getBroadcastIpAddresses()
     */
    public function testBroadcastIp($initial, $expected, $cidr)
    {
        $ip = IP::factory($initial);
        $this->assertSame($expected, $ip->getBroadcastIp($cidr)->getProtocolAppropriateAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidInRangeIpAddresses()
     */
    public function testInRange($first, $second, $cidr)
    {
        $first = IP::factory($first);
        $second = IP::factory($second);
        $this->assertTrue($first->inRange($second, $cidr));
    }

    /** @test */
    public function testDifferentVersionsAreInRange()
    {
        $first = IP::factory('127.0.0.1', new Strategy\Mapped);
        $second = IPv6::factory('::1234:5678:abcd:90ef');
        $this->assertTrue($first->inRange($second, 0));
    }

    /** @test */
    public function testDifferentByteLengthsAreNotInRange()
    {
        $first = IP::factory('127.0.0.1');
        $second = IPv4::factory('127.0.0.1');
        $this->expectException(WrongVersionException::class);
        $first->inRange($second, 0);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getCommonCidrValues()
     */
    public function testCommonCidr($first, $second, $expectedCidr)
    {
        $first = IP::factory($first);
        $second = IP::factory($second);
        $this->assertSame($expectedCidr, $first->getCommonCidr($second));
    }

    /** @test */
    public function testCommonCidrThrowsException()
    {
        $first = IP::factory('12.34.56.78');
        $second = IPv4::factory('12.34.56.78');
        $this->expectException(WrongVersionException::class);
        $first->getCommonCidr($second);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getLinkLocalIpAddresses()
     */
    public function testIsLinkLocal($value, $isLinkLocal)
    {
        $ip = IP::factory($value);
        $this->assertSame($isLinkLocal, $ip->isLinkLocal());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getMappedLoopbackIpAddresses()
     */
    public function testIsLoopbackMapped($value, $isLoopback)
    {
        $ip = IP::factory($value, new Strategy\Mapped);
        $this->assertSame($isLoopback, $ip->isLoopback());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getCompatibleLoopbackIpAddresses()
     */
    public function testIsLoopbackCompatible($value, $isLoopback)
    {
        $ip = IP::factory($value, new Strategy\Compatible);
        $this->assertSame($isLoopback, $ip->isLoopback());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getDerivedLoopbackIpAddresses()
     */
    public function testIsLoopbackDerived($value, $isLoopback)
    {
        $ip = IP::factory($value, new Strategy\Derived);
        $this->assertSame($isLoopback, $ip->isLoopback());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getMulticastIpAddresses()
     */
    public function testIsMulticast($value, $isMulticast)
    {
        $ip = IP::factory($value);
        $this->assertSame($isMulticast, $ip->isMulticast());

    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getPrivateUseIpAddresses()
     */
    public function testIsPrivateUse($value, $isPrivateUse)
    {
        $ip = IP::factory($value);
        $this->assertSame($isPrivateUse, $ip->isPrivateUse());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getUnspecifiedIpAddresses()
     */
    public function testIsUnspecified($value, $isUnspecified)
    {
        $ip = IP::factory($value);
        $this->assertSame($isUnspecified, $ip->isUnspecified());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidIpAddresses()
     */
    public function testStringCasting($value, $hex, $expanded, $compacted, $dot)
    {
        $ip = IP::factory($value);
        $dot !== null
            ? $this->assertSame($dot, (string) $ip)
            : $this->assertSame($compacted, (string) $ip);
    }
}
