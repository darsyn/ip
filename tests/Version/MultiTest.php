<?php declare(strict_types=1);

namespace Darsyn\IP\Tests\Version;

use Darsyn\IP\Exception\InvalidIpAddressException;
use Darsyn\IP\Exception\WrongVersionException;
use Darsyn\IP\IpInterface;
use Darsyn\IP\Tests\TestCase;
use Darsyn\IP\Version\Multi as IP;
use Darsyn\IP\Version\MultiVersionInterface;
use Darsyn\IP\Version\Version4Interface;
use Darsyn\IP\Version\Version6Interface;

class MultiTest extends TestCase
{
    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidIpAddresses()
     */
    public function testInstantiationWithValidAddresses($value): void
    {
        $ip = IP::factory($value);
        $this->assertInstanceOf(IpInterface::class, $ip);
        $this->assertInstanceOf(Version4Interface::class, $ip);
        $this->assertInstanceOf(Version6Interface::class, $ip);
        $this->assertInstanceOf(MultiVersionInterface::class, $ip);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidBinarySequences()
     */
    public function testBinarySequenceIsTheSameOnceInstantiated($value): void
    {
        $ip = IP::factory($value);
        $this->assertSame($value, $ip->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpAddresses()
     */
    public function testProtocolNotationConvertsToCorrectBinarySequence($value, $hex): void
    {
        $ip = IP::factory($value);
        $this->assertSame($hex, unpack('H*hex', $ip->getBinary())['hex']);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getInvalidIpAddresses()
     * @expectedException \Darsyn\IP\Exception\InvalidIpAddressException
     * @expectedExceptionMessage The IP address supplied is not valid.
     */
    public function testExceptionIsThrownOnInstantiationWithInvalidAddresses($value): void
    {
        try {
            $ip = IP::factory($value);
        } catch (InvalidIpAddressException $e) {
            $this->assertSame($value, $e->getSuppliedIp());
            throw $e;
        }
        $this->fail();
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidIpAddresses()
     */
    public function testGetBinaryAlwaysReturnsA16ByteString($value): void
    {
        $ip = IP::factory($value);
        $this->assertSame(16, strlen(bin2hex($ip->getBinary())) / 2);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidIpAddresses()
     */
    public function testGetCompactedAddressReturnsCorrectString($value, $hex, $expanded, $compacted): void
    {
        $ip = IP::factory($value);
        $this->assertSame($compacted, $ip->getCompactedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpAddresses()
     */
    public function testGetExpandedAddressReturnsCorrectString($value, $hex, $expanded): void
    {
        $ip = IP::factory($value);
        $this->assertSame($expanded, $ip->getExpandedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidIpVersion4Addresses()
     */
    public function testDotAddressReturnsCorrectString($value, $hex, $expanded, $compacted, $dot): void
    {
        $ip = IP::factory($value);
        $this->assertSame($dot, $ip->getDotAddress());
    }

    /**
     * @test
     * @expectedException \Darsyn\IP\Exception\WrongVersionException
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidIpVersion6Addresses()
     */
    public function testDotAddressThrowsExceptionForNonVersion4Addresses($value): void
    {
        try {
            $ip = IP::factory($value);
            $ip->getDotAddress();
        } catch (WrongVersionException $e) {
            $this->assertSame($ip->getBinary(), $e->getSuppliedIp());
            $this->assertSame(4, $e->getExpectedVersion());
            $this->assertSame(6, $e->getActualVersion());
            throw $e;
        }
        $this->fail();
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getNetworkIpAddresses()
     */
    public function testNetworkIp($initial, $expected, $cidr): void
    {
        $ip = IP::factory($initial);
        $this->assertSame($expected, $ip->getNetworkIp($cidr)->getProtocolAppropriateAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getBroadcastIpAddresses()
     */
    public function testBroadcastIp($initial, $expected, $cidr): void
    {
        $ip = IP::factory($initial);
        $this->assertSame($expected, $ip->getBroadcastIp($cidr)->getProtocolAppropriateAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidInRangeIpAddresses()
     */
    public function testInRange($first, $second, $cidr): void
    {
        $first = IP::factory($first);
        $second = IP::factory($second);
        $this->assertTrue($first->inRange($second, $cidr));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getLinkLocalIpAddresses()
     */
    public function testIsLinkLocal($value, $isLinkLocal): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isLinkLocal, $ip->isLinkLocal());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getLoopbackIpAddresses()
     */
    public function testIsLoopback($value, $isLoopback): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isLoopback, $ip->isLoopback());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getMulticastIpAddresses()
     */
    public function testIsMulticast($value, $isMulticast): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isMulticast, $ip->isMulticast());

    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getPrivateUseIpAddresses()
     */
    public function testIsPrivateUse($value, $isPrivateUse): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isPrivateUse, $ip->isPrivateUse());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getUnspecifiedIpAddresses()
     */
    public function testIsUnspecified($value, $isUnspecified): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isUnspecified, $ip->isUnspecified());
    }
}
