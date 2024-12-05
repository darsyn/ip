<?php

namespace Darsyn\IP\Tests\Version;

use Darsyn\IP\Exception\InvalidCidrException;
use Darsyn\IP\Exception\InvalidIpAddressException;
use Darsyn\IP\Exception\WrongVersionException;
use Darsyn\IP\IpInterface;
use Darsyn\IP\Tests\DataProvider\IPv4 as IPv4DataProvider;
use Darsyn\IP\Version\IPv4 as IP;
use Darsyn\IP\Version\IPv6;
use Darsyn\IP\Version\Version4Interface;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class IPv4Test extends TestCase
{
    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testInstantiationWithValidAddresses($value)
    {
        $ip = IP::factory($value);
        $this->assertInstanceOf(IpInterface::class, $ip);
        $this->assertInstanceOf(Version4Interface::class, $ip);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidBinarySequences()
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidBinarySequences')]
    public function testBinarySequenceIsTheSameOnceInstantiated($value)
    {
        $ip = IP::factory($value);
        $this->assertSame($value, $ip->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     * @param string $value
     * @param string $expectedHex
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testProtocolNotationConvertsToCorrectBinarySequence($value, $expectedHex)
    {
        $ip = IP::factory($value);
        $actualHex = unpack('H*hex', $ip->getBinary());
        $this->assertSame($expectedHex, is_array($actualHex) ? $actualHex['hex'] : null);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getInvalidIpAddresses()
     * @param mixed $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getInvalidIpAddresses')]
    public function testExceptionIsThrownOnInstantiationWithInvalidAddresses($value)
    {
        $this->expectException(\Darsyn\IP\Exception\InvalidIpAddressException::class);
        $this->expectExceptionMessage('The IP address supplied is not valid.');
        try {
            // @phpstan-ignore argument.type
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
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testGetBinaryAlwaysReturnsA4ByteString($value)
    {
        $ip = IP::factory($value);
        $this->assertSame(4, strlen(bin2hex($ip->getBinary())) / 2);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     * @param string $value
     * @param string $expectedHex
     * @param string $expectedDot
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testDotAddressReturnsCorrectString($value, $expectedHex, $expectedDot)
    {
        $ip = IP::factory($value);
        $this->assertSame($expectedDot, $ip->getDotAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testGetVersionAlwaysReturns4($value)
    {
        $ip = IP::factory($value);
        $this->assertSame(4, $ip->getVersion());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testIsVersionOnlyReturnsTrueFor4($value)
    {
        $ip = IP::factory($value);
        $this->assertTrue($ip->isVersion(4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testIsVersionOnlyReturnsFalseFor6($value)
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isVersion(6));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testIsVersion4AlwaysReturnsTrue($value)
    {
        $ip = IP::factory($value);
        $this->assertTrue($ip->isVersion4());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testIsVersion6AlwaysReturnsFalse($value)
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isVersion6());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidCidrValues()
     * @param int $cidr
     * @param string $expectedMaskHex
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidCidrValues')]
    public function testCidrMasks($cidr, $expectedMaskHex)
    {
        $ip = IP::factory('12.34.56.78');
        $reflect = new \ReflectionClass($ip);
        $method = $reflect->getMethod('generateBinaryMask');
        $method->setAccessible(true);
        // @phpstan-ignore argument.type
        $actualMask = unpack('H*hex', $method->invoke($ip, $cidr, 4));
        $this->assertSame($expectedMaskHex, is_array($actualMask) ? $actualMask['hex'] : null);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getInvalidCidrValues()
     * @param mixed $cidr
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getInvalidCidrValues')]
    public function testExceptionIsThrownFromInvalidCidrValues($cidr)
    {
        $this->expectException(\Darsyn\IP\Exception\InvalidCidrException::class);
        $this->expectExceptionMessage('The supplied CIDR is not valid; it must be an integer (between 0 and 32).');
        $ip = IP::factory('12.34.56.78');
        $reflect = new \ReflectionClass($ip);
        $method = $reflect->getMethod('generateBinaryMask');
        $method->setAccessible(true);
        try {
            $method->invoke($ip, $cidr, 4);
        // @phpstan-ignore catch.neverThrown
        } catch (InvalidCidrException $e) {
            $this->assertSame($cidr, $e->getSuppliedCidr());
            throw $e;
        }
        $this->fail();
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getNetworkIpAddresses()
     * @param string $expected
     * @param int $cidr
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getNetworkIpAddresses')]
    public function testNetworkIp($expected, $cidr)
    {
        $ip = IP::factory('12.34.56.78');
        $this->assertSame($expected, $ip->getNetworkIp($cidr)->getDotAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getBroadcastIpAddresses()
     * @param string $expected
     * @param int $cidr
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getBroadcastIpAddresses')]
    public function testBroadcastIp($expected, $cidr)
    {
        $ip = IP::factory('12.34.56.78');
        $this->assertSame($expected, $ip->getBroadcastIp($cidr)->getDotAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidInRangeIpAddresses()
     * @param string $first
     * @param string $second
     * @param int $cidr
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidInRangeIpAddresses')]
    public function testInRange($first, $second, $cidr)
    {
        $first = IP::factory($first);
        $second = IP::factory($second);
        $this->assertTrue($first->inRange($second, $cidr));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getInvalidCidrValues()
     * @param mixed $cidr
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getInvalidCidrValues')]
    public function testInRangeThrowsExceptionOnInvalidCidr($cidr)
    {
        $first = IP::factory('12.34.56.78');
        $second = IP::factory('12.34.56.78');
        $this->expectException(InvalidCidrException::class);
        // @phpstan-ignore argument.type
        $first->inRange($second, $cidr);
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testDifferentVersionsAreNotInRange()
    {
        $ip = IP::factory('12.34.56.78');
        $other = IPv6::factory('::12.34.56.78');
        $this->expectException(WrongVersionException::class);
        $ip->inRange($other, 0);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getCommonCidrValues()
     * @param string $first
     * @param string $second
     * @param int $expectedCidr
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getCommonCidrValues')]
    public function testCommonCidr($first, $second, $expectedCidr)
    {
        $first = IP::factory($first);
        $second = IP::factory($second);
        $this->assertSame($expectedCidr, $first->getCommonCidr($second));
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testCommonCidrThrowsException()
    {
        $first = IP::factory('12.34.56.78');
        $second = IPv6::factory('2001:db8::a60:8a2e:370:7334');
        $this->expectException(WrongVersionException::class);
        $first->getCommonCidr($second);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testIsMappedAlwaysReturnsFalse($value)
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isMapped());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testIsDerivedAlwaysReturnsFalse($value)
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isDerived());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testIsCompatibleAlwaysReturnsFalse($value)
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isCompatible());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testIsEmbeddedAlwaysReturnsFalse($value)
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isEmbedded());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getLinkLocalIpAddresses()
     * @param string $value
     * @param bool $isLinkLocal
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getLinkLocalIpAddresses')]
    public function testIsLinkLocal($value, $isLinkLocal)
    {
        $ip = IP::factory($value);
        $this->assertSame($isLinkLocal, $ip->isLinkLocal());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getLoopbackIpAddresses()
     * @param string $value
     * @param bool $isLoopback
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getLoopbackIpAddresses')]
    public function testIsLoopback($value, $isLoopback)
    {
        $ip = IP::factory($value);
        $this->assertSame($isLoopback, $ip->isLoopback());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getMulticastIpAddresses()
     * @param string $value
     * @param bool $isMulticast
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getMulticastIpAddresses')]
    public function testIsMulticast($value, $isMulticast)
    {
        $ip = IP::factory($value);
        $this->assertSame($isMulticast, $ip->isMulticast());

    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getPrivateUseIpAddresses()
     * @param string $value
     * @param bool $isPrivateUse
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getPrivateUseIpAddresses')]
    public function testIsPrivateUse($value, $isPrivateUse)
    {
        $ip = IP::factory($value);
        $this->assertSame($isPrivateUse, $ip->isPrivateUse());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getUnspecifiedIpAddresses()
     * @param string $value
     * @param bool $isUnspecified
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getUnspecifiedIpAddresses')]
    public function testIsUnspecified($value, $isUnspecified)
    {
        $ip = IP::factory($value);
        $this->assertSame($isUnspecified, $ip->isUnspecified());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getBenchmarkingIpAddresses()
     * @param string $value
     * @param bool $isBenchmarking
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getBenchmarkingIpAddresses')]
    public function testIsBenchmarking($value, $isBenchmarking)
    {
        $ip = IP::factory($value);
        $this->assertSame($isBenchmarking, $ip->isBenchmarking());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getDocumentationIpAddresses()
     * @param string $value
     * @param bool $isDocumentation
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getDocumentationIpAddresses')]
    public function testIsDocumentation($value, $isDocumentation)
    {
        $ip = IP::factory($value);
        $this->assertSame($isDocumentation, $ip->isDocumentation());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getPublicUseIpAddresses()
     * @param string $value
     * @param bool $isPublicUse
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getPublicUseIpAddresses')]
    public function testIsPublicUse($value, $isPublicUse)
    {
        $ip = IP::factory($value);
        $this->assertSame($isPublicUse, $ip->isPublicUse());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getIsBroadcastIpAddresses()
     * @param string $value
     * @param bool $isBroadcast
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getIsBroadcastIpAddresses')]
    public function testIsBroadcast($value, $isBroadcast)
    {
        $ip = IP::factory($value);
        $this->assertSame($isBroadcast, $ip->isBroadcast());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getSharedIpAddresses()
     * @param string $value
     * @param bool $isShared
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getSharedIpAddresses')]
    public function testIsShared($value, $isShared)
    {
        $ip = IP::factory($value);
        $this->assertSame($isShared, $ip->isShared());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getFutureReservedIpAddresses()
     * @param string $value
     * @param bool $isFutureReserved
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getFutureReservedIpAddresses')]
    public function testIsFutureReserved($value, $isFutureReserved)
    {
        $ip = IP::factory($value);
        $this->assertSame($isFutureReserved, $ip->isFutureReserved());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     * @param string $value
     * @param string $expectedHex
     * @param string $expectedDot
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testStringCasting($value, $expectedHex, $expectedDot)
    {
        $ip = IP::factory($value);
        $this->assertSame($expectedDot, (string) $ip);
    }
}
