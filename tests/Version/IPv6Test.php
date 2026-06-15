<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\Version;

use Darsyn\IP\Exception\InvalidCidrException;
use Darsyn\IP\Exception\InvalidIpAddressException;
use Darsyn\IP\Exception\WrongVersionException;
use Darsyn\IP\Formatter\ConsistentFormatter;
use Darsyn\IP\Formatter\NativeFormatter;
use Darsyn\IP\IpInterface;
use Darsyn\IP\Strategy\Mapped;
use Darsyn\IP\Tests\DataProvider\IPv4 as IPv4DataProvider;
use Darsyn\IP\Tests\DataProvider\IPv6 as IPv6DataProvider;
use Darsyn\IP\Tests\Stub\StubFormatter;
use Darsyn\IP\Tests\TestCase;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Version\IPv4;
use Darsyn\IP\Version\IPv6 as IP;
use Darsyn\IP\Version\Multi;
use Darsyn\IP\Version\Version6Interface;
use PHPUnit\Framework\Attributes as PHPUnit;

class IPv6Test extends TestCase
{
    /** @before */
    #[PHPUnit\Before]
    public function resetProtocolFormatter(): void
    {
        IP::setProtocolFormatter(new ConsistentFormatter());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidIpAddresses')]
    public function testInstantiationWithValidAddresses(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::factory($value);
        $this->assertInstanceOf(IpInterface::class, $ip);
        $this->assertInstanceOf(Version6Interface::class, $ip);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidBinarySequences')]
    public function testBinarySequenceIsTheSameOnceInstantiated(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::factory($value);
        $this->assertSame($value, $ip->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testProtocolNotationConvertsToCorrectBinarySequence(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::factory($value);
        $actualHex = \unpack('H*hex', $ip->getBinary());
        $this->assertSame($hex, \is_array($actualHex) ? $actualHex['hex'] : null);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getInvalidIpAddresses')]
    public function testExceptionIsThrownOnInstantiationWithInvalidAddresses(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\InvalidIpAddressException::class);
        $this->legacyExpectExceptionMessage('The IP address supplied is not valid.');
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
     * @covers \Darsyn\IP\Version\IPv6::fromEmbedded()
     * @covers \Darsyn\IP\Version\Multi::factory()
     * @covers \Darsyn\IP\Version\Multi::getBinary()
     */
    #[PHPUnit\Test]
    public function testInstantiationFromEmbeddedIpAddress(): void
    {
        try {
            $ip = IP::factory('12.34.56.78');
            $this->fail('IPv6 factory should not accept IPv4 addresses.');
        } catch (InvalidIpAddressException $e) {
        }

        // IPv4 address can be embedded into IPv6 objects using the fromEmbedded() static instantiator.
        $embedded = IP::fromEmbedded('12.34.56.78', new Mapped());
        // But IPv6 objects should ignore the fact that it's embedded and only work with the full IPv6 address.
        $this->assertSame('0000:1fff:ffff:ffff:ffff:ffff:ffff:ffff', $embedded->getBroadcastIp(19)->getExpandedAddress());

        // Multi objects understand both IPv4 and IPv6 addresses.
        $multi = Multi::factory('12.34.56.78', new Mapped());
        // So therefore, if a Multi object detects that it holds an embedded IPv4 address it will attempt to work with
        // the IPv4 address before falling back on the full IPv6 address.
        $this->assertSame('0000:0000:0000:0000:0000:ffff:0c22:3fff', $multi->getBroadcastIp(19)->getExpandedAddress());
        $this->assertSame('12.34.63.255', $multi->getBroadcastIp(19)->getDotAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidIpAddresses')]
    public function testGetBinaryAlwaysReturnsA16ByteString(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::factory($value);
        $this->assertSame(16, \strlen(\bin2hex($ip->getBinary())) / 2);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidIpAddresses')]
    public function testGetCompactedAddressReturnsCorrectString(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::factory($value);
        $this->assertSame($compacted, $ip->getCompactedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testGetExpandedAddressReturnsCorrectString(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::factory($value);
        $this->assertSame($expanded, $ip->getExpandedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidIpAddresses')]
    public function testGetVersionAlwaysReturns6(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::factory($value);
        $this->assertSame(6, $ip->getVersion());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidIpAddresses')]
    public function testIsVersionOnlyReturnsTrueFor6(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::factory($value);
        $this->assertTrue($ip->isVersion(6));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidIpAddresses')]
    public function testIsVersionOnlyReturnsFalseFor4(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isVersion(4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidIpAddresses')]
    public function testIsVersion6AlwaysReturnsTrue(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::factory($value);
        $this->assertTrue($ip->isVersion6());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidIpAddresses')]
    public function testIsVersion4AlwaysReturnsFalse(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isVersion4());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidCidrValues()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidCidrValues')]
    public function testCidrMasks(int $cidr, string $expectedMaskHex): void
    {
        $mask = Binary::mask($cidr, 16);
        $actualMask = \unpack('H*hex', $mask);
        $this->assertSame($expectedMaskHex, \is_array($actualMask) ? $actualMask['hex'] : null);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getOutOfRangeCidrValues()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getOutOfRangeCidrValues')]
    public function testExceptionIsThrownFromOutOfRangeCidrValues(int $cidr): void
    {
        $this->expectException(\Darsyn\IP\Exception\InvalidCidrException::class);
        $this->legacyExpectExceptionMessage('The supplied CIDR is not valid; it must be an integer (between 0 and 128).');
        try {
            Binary::mask($cidr, 16);
        } catch (InvalidCidrException $e) {
            $this->assertSame($cidr, $e->getSuppliedCidr());
            throw $e;
        }
        $this->fail();
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getNetworkIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getNetworkIpAddresses')]
    public function testNetworkIp(string $expected, int $cidr): void
    {
        $ip = IP::factory('2001:db8::a60:8a2e:370:7334');
        $this->assertSame($expected, $ip->getNetworkIp($cidr)->getCompactedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getBroadcastIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getBroadcastIpAddresses')]
    public function testBroadcastIp(string $expected, int $cidr): void
    {
        $ip = IP::factory('2001:db8::a60:8a2e:370:7334');
        $this->assertSame($expected, $ip->getBroadcastIp($cidr)->getCompactedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidInRangeIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidInRangeIpAddresses')]
    public function testInRange(string $first, string $second, int $cidr): void
    {
        $first = IP::factory($first);
        $second = IP::factory($second);
        $this->assertTrue($first->inRange($second, $cidr));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testDifferentVersionsAreNotInRange(): void
    {
        $ip = IP::factory('::12.34.56.78');
        $other = IPv4::factory('12.34.56.78');
        $this->expectException(WrongVersionException::class);
        $ip->inRange($other, 0);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getCommonCidrValues()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getCommonCidrValues')]
    public function testCommonCidr(string $first, string $second, int $expectedCidr): void
    {
        $first = IP::factory($first);
        $second = IP::factory($second);
        $this->assertSame($expectedCidr, $first->getCommonCidr($second));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getCommonCidrValues()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getCommonCidrValues')]
    public function testEmbeddedCommonCidr(string $first, string $second, int $expectedCidr): void
    {
        $first = IP::fromEmbedded($first);
        $second = IP::fromEmbedded($second);
        $this->assertSame(96 + $expectedCidr, $first->getCommonCidr($second));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testCommonCidrThrowsException(): void
    {
        $first = IP::factory('2001:db8::a60:8a2e:370:7334');
        $second = IPv4::factory('12.34.56.78');
        $this->expectException(WrongVersionException::class);
        $first->getCommonCidr($second);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getMappedIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getMappedIpAddresses')]
    public function testIsMapped(string $value, bool $isMapped): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isMapped, $ip->isMapped());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getDerivedIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getDerivedIpAddresses')]
    public function testIsDerived(string $value, bool $isDerived): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isDerived, $ip->isDerived());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getCompatibleIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getCompatibleIpAddresses')]
    public function testIsCompatible(string $value, bool $isCompatible): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isCompatible, $ip->isCompatible());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidIpAddresses')]
    public function testIsEmbeddedAlwaysReturnsFalse(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isEmbedded());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getLinkLocalIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getLinkLocalIpAddresses')]
    public function testIsLinkLocal(string $value, bool $isLinkLocal): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isLinkLocal, $ip->isLinkLocal());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getLoopbackIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getLoopbackIpAddresses')]
    public function testIsLoopback(string $value, bool $isLoopback): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isLoopback, $ip->isLoopback());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getMulticastIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getMulticastIpAddresses')]
    public function testIsMulticast(string $value, bool $isMulticast): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isMulticast, $ip->isMulticast());

    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getPrivateUseIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getPrivateUseIpAddresses')]
    public function testIsPrivateUse(string $value, bool $isPrivateUse): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isPrivateUse, $ip->isPrivateUse());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getUnspecifiedIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getUnspecifiedIpAddresses')]
    public function testIsUnspecified(string $value, bool $isUnspecified): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isUnspecified, $ip->isUnspecified());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getBenchmarkingIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getBenchmarkingIpAddresses')]
    public function testIsBenchmarking(string $value, bool $isBenchmarking): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isBenchmarking, $ip->isBenchmarking());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getDocumentationIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getDocumentationIpAddresses')]
    public function testIsDocumentation(string $value, bool $isDocumentation): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isDocumentation, $ip->isDocumentation());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getGloballyReachableIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getGloballyReachableIpAddresses')]
    public function testIsGloballyReachable(string $value, bool $isGloballyReachable): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isGloballyReachable, $ip->isGloballyReachable());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getUniqueLocalIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getUniqueLocalIpAddresses')]
    public function testIsUniqueLocal(string $value, bool $isUniqueLocal): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isUniqueLocal, $ip->isUniqueLocal());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getUnicastIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getUnicastIpAddresses')]
    public function testIsUnicast(string $value, bool $isUnicast): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isUnicast, $ip->isUnicast());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getUnicastGlobalIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getUnicastGlobalIpAddresses')]
    public function testIsUnicastGlobal(string $value, bool $isUnicastGlobal): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isUnicastGlobal, $ip->isUnicastGlobal());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidIpAddresses')]
    public function testStringCasting(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::factory($value);
        $this->assertSame($compacted, (string) $ip);
    }

    /** @test */
    #[PHPUnit\Test]
    public function testPerCallFormatterOverridesGlobal(): void
    {
        $ip = IP::factory('2001:db8::a60:8a2e:370:7334');
        $this->assertSame(StubFormatter::SENTINEL, $ip->getCompactedAddress(new StubFormatter()));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testPerCallNativeFormatterProducesNativeOutput(): void
    {
        $ip = IP::factory('::ffff:c22:384e');
        $this->assertSame('::ffff:c22:384e', $ip->getCompactedAddress());
        $this->assertSame('::ffff:12.34.56.78', $ip->getCompactedAddress(new NativeFormatter()));
        $this->assertSame('::ffff:c22:384e', $ip->getCompactedAddress());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testExplicitNullPerCallFormatterFallsBackToGlobal(): void
    {
        $ip = IP::factory('::ffff:c22:384e');
        $this->assertSame('::ffff:c22:384e', $ip->getCompactedAddress(null));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testInvalidPerCallFormatterTriggersDeprecationAndFallsBack(): void
    {
        $ip = IP::factory('2001:db8::a60:8a2e:370:7334');
        $result = null;
        $message = $this->captureDeprecation(static function () use ($ip, &$result): void {
            $result = $ip->getCompactedAddress(new \stdClass());
        });
        $this->assertNotNull($message);
        $this->assertSame('2001:db8::a60:8a2e:370:7334', $result);
    }
}
