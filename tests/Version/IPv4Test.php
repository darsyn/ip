<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\Version;

use Darsyn\IP\Exception\InvalidCidrException;
use Darsyn\IP\Exception\InvalidIpAddressException;
use Darsyn\IP\Exception\WrongVersionException;
use Darsyn\IP\IpInterface;
use Darsyn\IP\Tests\DataProvider\IPv4 as IPv4DataProvider;
use Darsyn\IP\Tests\TestCase;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Version\IPv4 as IP;
use Darsyn\IP\Version\IPv6;
use Darsyn\IP\Version\Version4Interface;
use PHPUnit\Framework\Attributes as PHPUnit;

class IPv4Test extends TestCase
{
    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testInstantiationWithValidAddresses(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::factory($value);
        $this->assertInstanceOf(IpInterface::class, $ip);
        $this->assertInstanceOf(Version4Interface::class, $ip);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidBinarySequences')]
    public function testBinarySequenceIsTheSameOnceInstantiated(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::factory($value);
        $this->assertSame($value, $ip->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testProtocolNotationConvertsToCorrectBinarySequence(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::factory($value);
        $actualHex = \unpack('H*hex', $ip->getBinary());
        $this->assertSame($expectedHex, \is_array($actualHex) ? $actualHex['hex'] : null);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getInvalidIpAddresses')]
    public function testExceptionIsThrownOnInstantiationWithInvalidAddresses(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\InvalidIpAddressException::class);
        $this->legacyExpectExceptionMessage('The IP address supplied is not valid.');
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
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testGetBinaryAlwaysReturnsA4ByteString(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::factory($value);
        $this->assertSame(4, \strlen(\bin2hex($ip->getBinary())) / 2);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testDotAddressReturnsCorrectString(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::factory($value);
        $this->assertSame($expectedDot, $ip->getDotAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testGetVersionAlwaysReturns4(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::factory($value);
        $this->assertSame(4, $ip->getVersion());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testIsVersionOnlyReturnsTrueFor4(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::factory($value);
        $this->assertTrue($ip->isVersion(4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testIsVersionOnlyReturnsFalseFor6(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isVersion(6));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testIsVersion4AlwaysReturnsTrue(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::factory($value);
        $this->assertTrue($ip->isVersion4());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testIsVersion6AlwaysReturnsFalse(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isVersion6());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidCidrValues()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidCidrValues')]
    public function testCidrMasks(int $cidr, string $expectedMaskHex): void
    {
        $mask = Binary::mask($cidr, 4);
        $actualMask = \unpack('H*hex', $mask);
        $this->assertSame($expectedMaskHex, \is_array($actualMask) ? $actualMask['hex'] : null);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getOutOfRangeCidrValues()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getOutOfRangeCidrValues')]
    public function testExceptionIsThrownFromOutOfRangeCidrValues(int $cidr): void
    {
        $this->expectException(\Darsyn\IP\Exception\InvalidCidrException::class);
        $this->legacyExpectExceptionMessage('The supplied CIDR is not valid; it must be an integer (between 0 and 32).');
        try {
            Binary::mask($cidr, 4);
        } catch (InvalidCidrException $e) {
            $this->assertSame($cidr, $e->getSuppliedCidr());
            throw $e;
        }
        $this->fail();
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getNetworkIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getNetworkIpAddresses')]
    public function testNetworkIp(string $expected, int $cidr): void
    {
        $ip = IP::factory('12.34.56.78');
        $this->assertSame($expected, $ip->getNetworkIp($cidr)->getDotAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getBroadcastIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getBroadcastIpAddresses')]
    public function testBroadcastIp(string $expected, int $cidr): void
    {
        $ip = IP::factory('12.34.56.78');
        $this->assertSame($expected, $ip->getBroadcastIp($cidr)->getDotAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidInRangeIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidInRangeIpAddresses')]
    public function testInRange(string $first, string $second, int $cidr): void
    {
        $first = IP::factory($first);
        $second = IP::factory($second);
        $this->assertTrue($first->inRange($second, $cidr));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getOutOfRangeCidrValues()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getOutOfRangeCidrValues')]
    public function testInRangeThrowsExceptionOnOutOfRangeCidr(int $cidr): void
    {
        $first = IP::factory('12.34.56.78');
        $second = IP::factory('12.34.56.78');
        $this->expectException(InvalidCidrException::class);
        $first->inRange($second, $cidr);
    }

    /** @test */
    #[PHPUnit\Test]
    public function testDifferentVersionsAreNotInRange(): void
    {
        $ip = IP::factory('12.34.56.78');
        $other = IPv6::factory('::12.34.56.78');
        $this->expectException(WrongVersionException::class);
        $ip->inRange($other, 0);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getCommonCidrValues()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getCommonCidrValues')]
    public function testCommonCidr(string $first, string $second, int $expectedCidr): void
    {
        $first = IP::factory($first);
        $second = IP::factory($second);
        $this->assertSame($expectedCidr, $first->getCommonCidr($second));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testCommonCidrThrowsException(): void
    {
        $first = IP::factory('12.34.56.78');
        $second = IPv6::factory('2001:db8::a60:8a2e:370:7334');
        $this->expectException(WrongVersionException::class);
        $first->getCommonCidr($second);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testIsMappedAlwaysReturnsFalse(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isMapped());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testIsDerivedAlwaysReturnsFalse(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isDerived());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testIsCompatibleAlwaysReturnsFalse(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isCompatible());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testIsEmbeddedAlwaysReturnsFalse(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isEmbedded());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getLinkLocalIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getLinkLocalIpAddresses')]
    public function testIsLinkLocal(string $value, bool $isLinkLocal): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isLinkLocal, $ip->isLinkLocal());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getLoopbackIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getLoopbackIpAddresses')]
    public function testIsLoopback(string $value, bool $isLoopback): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isLoopback, $ip->isLoopback());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getMulticastIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getMulticastIpAddresses')]
    public function testIsMulticast(string $value, bool $isMulticast): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isMulticast, $ip->isMulticast());

    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getPrivateUseIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getPrivateUseIpAddresses')]
    public function testIsPrivateUse(string $value, bool $isPrivateUse): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isPrivateUse, $ip->isPrivateUse());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getUnspecifiedIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getUnspecifiedIpAddresses')]
    public function testIsUnspecified(string $value, bool $isUnspecified): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isUnspecified, $ip->isUnspecified());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getBenchmarkingIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getBenchmarkingIpAddresses')]
    public function testIsBenchmarking(string $value, bool $isBenchmarking): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isBenchmarking, $ip->isBenchmarking());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getDocumentationIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getDocumentationIpAddresses')]
    public function testIsDocumentation(string $value, bool $isDocumentation): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isDocumentation, $ip->isDocumentation());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getGloballyReachableIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getGloballyReachableIpAddresses')]
    public function testIsGloballyReachable(string $value, bool $isGloballyReachable): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isGloballyReachable, $ip->isGloballyReachable());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getIsBroadcastIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getIsBroadcastIpAddresses')]
    public function testIsBroadcast(string $value, bool $isBroadcast): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isBroadcast, $ip->isBroadcast());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getSharedIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getSharedIpAddresses')]
    public function testIsShared(string $value, bool $isShared): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isShared, $ip->isShared());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getFutureReservedIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getFutureReservedIpAddresses')]
    public function testIsFutureReserved(string $value, bool $isFutureReserved): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isFutureReserved, $ip->isFutureReserved());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidIpAddresses')]
    public function testStringCasting(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::factory($value);
        $this->assertSame($expectedDot, (string) $ip);
    }
}
