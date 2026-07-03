<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\Version;

use Darsyn\IP\Contracts\ArithmeticInterface;
use Darsyn\IP\Contracts\Classification6Interface;
use Darsyn\IP\Contracts\ClassificationInterface;
use Darsyn\IP\Contracts\ComparisonInterface;
use Darsyn\IP\Contracts\Factory4Interface;
use Darsyn\IP\Contracts\FactoryInterface;
use Darsyn\IP\Contracts\Output6Interface;
use Darsyn\IP\Contracts\OutputInterface;
use Darsyn\IP\Contracts\StrategyDetectionInterface;
use Darsyn\IP\Contracts\VersionIdentityInterface;
use Darsyn\IP\Exception\InvalidBinaryException;
use Darsyn\IP\Exception\InvalidCidrException;
use Darsyn\IP\Exception\InvalidIpAddressException;
use Darsyn\IP\Exception\OverflowException;
use Darsyn\IP\Exception\WrongVersionException;
use Darsyn\IP\Formatter\ConsistentFormatter;
use Darsyn\IP\Formatter\NativeFormatter;
use Darsyn\IP\IpInterface;
use Darsyn\IP\Strategy\Derived;
use Darsyn\IP\Strategy\Mapped;
use Darsyn\IP\Tests\DataProvider\IPv4 as IPv4DataProvider;
use Darsyn\IP\Tests\DataProvider\IPv6 as IPv6DataProvider;
use Darsyn\IP\Tests\DataProvider\Strategy\Nat64 as Nat64DataProvider;
use Darsyn\IP\Tests\DataProvider\Strategy\Teredo as TeredoDataProvider;
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

    /** @test */
    #[PHPUnit\Test]
    public function testImplementsCapabilityInterfaces(): void
    {
        $ip = IP::fromProtocol('::1');
        $this->assertInstanceOf(VersionIdentityInterface::class, $ip);
        $this->assertInstanceOf(ComparisonInterface::class, $ip);
        $this->assertInstanceOf(ArithmeticInterface::class, $ip);
        $this->assertInstanceOf(OutputInterface::class, $ip);
        $this->assertInstanceOf(Output6Interface::class, $ip);
        $this->assertInstanceOf(ClassificationInterface::class, $ip);
        $this->assertInstanceOf(Classification6Interface::class, $ip);
        $this->assertInstanceOf(FactoryInterface::class, $ip);
        $this->assertInstanceOf(StrategyDetectionInterface::class, $ip);
        // fromInteger() is version 4 only; IPv6 deliberately does not gain it.
        $this->assertNotInstanceOf(Factory4Interface::class, $ip);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testInstantiationWithValidAddresses(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertInstanceOf(IpInterface::class, $ip);
        $this->assertInstanceOf(Version6Interface::class, $ip);
    }

    /**
     * @test
     * @deprecated Retains coverage of the deprecated factory() raw-binary path.
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
     * @deprecated Retains coverage of the deprecated factory() protocol path.
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
     * @deprecated Retains coverage of the deprecated factory() validation path.
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
     * @covers \Darsyn\IP\Version\Multi::tryFromProtocol()
     * @covers \Darsyn\IP\Version\Multi::fromProtocol()
     * @covers \Darsyn\IP\Version\Multi::getBinary()
     */
    #[PHPUnit\Test]
    public function testInstantiationFromEmbeddedIpAddress(): void
    {
        try {
            $ip = IP::fromProtocol('12.34.56.78');
            $this->fail('IPv6 fromProtocol() should not accept IPv4 addresses.');
        } catch (InvalidIpAddressException $e) {
        }

        // IPv4 address can be embedded into IPv6 objects using the fromEmbedded() static instantiator.
        $embedded = IP::fromEmbedded('12.34.56.78', new Mapped());
        // But IPv6 objects should ignore the fact that it's embedded and only work with the full IPv6 address.
        $this->assertSame('0000:1fff:ffff:ffff:ffff:ffff:ffff:ffff', $embedded->getBroadcastIp(19)->getExpandedAddress());

        // Multi objects understand both IPv4 and IPv6 addresses.
        $multi = Multi::fromProtocol('12.34.56.78', new Mapped());
        // So therefore, if a Multi object detects that it holds an embedded IPv4 address it will attempt to work with
        // the IPv4 address before falling back on the full IPv6 address.
        $this->assertSame('0000:0000:0000:0000:0000:ffff:0c22:3fff', $multi->getBroadcastIp(19)->getExpandedAddress());
        $this->assertSame('12.34.63.255', $multi->getBroadcastIp(19)->getDotAddress());
    }

    /**
     * @test
     * @covers \Darsyn\IP\Version\IPv6::fromEmbedded()
     * @covers \Darsyn\IP\Version\Multi::tryFromProtocol()
     * @covers \Darsyn\IP\Version\Multi::fromBinary()
     */
    #[PHPUnit\Test]
    public function testFromEmbeddedAcceptsBinarySequence(): void
    {
        // fromEmbedded() accepts a raw binary sequence as well as protocol notation, remaining
        // backwards compatible with the deprecated factory() it used to delegate to.
        $fromProtocol = IP::fromEmbedded('12.34.56.78', new Mapped());
        $fromBinary = IP::fromEmbedded("\x0c\x22\x38\x4e", new Mapped());
        $this->assertSame($fromProtocol->getBinary(), $fromBinary->getBinary());
        $this->assertSame(
            $fromProtocol->getBinary(),
            IP::fromEmbedded($fromProtocol->getBinary(), new Mapped())->getBinary()
        );
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testGetBinaryAlwaysReturnsA16ByteString(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame(16, \strlen(\bin2hex($ip->getBinary())) / 2);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testGetCompactedAddressReturnsCorrectString(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
        $this->assertSame($expanded, $ip->getExpandedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testGetVersionAlwaysReturns6(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame(6, $ip->getVersion());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testIsVersionOnlyReturnsTrueFor6(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertTrue($ip->isVersion(6));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testIsVersionOnlyReturnsFalseFor4(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertFalse($ip->isVersion(4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testIsVersion6AlwaysReturnsTrue(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertTrue($ip->isVersion6());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testIsVersion4AlwaysReturnsFalse(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol('2001:db8::a60:8a2e:370:7334');
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
        $ip = IP::fromProtocol('2001:db8::a60:8a2e:370:7334');
        $this->assertSame($expected, $ip->getBroadcastIp($cidr)->getCompactedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getOffsetAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getOffsetAddresses')]
    public function testOffset(string $start, int $offset, string $expected): void
    {
        $result = IP::fromProtocol($start)->offset($offset);
        $this->assertInstanceOf(IP::class, $result);
        $this->assertSame($expected, $result->getCompactedAddress());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testNextAndPreviousAreOffsetByOne(): void
    {
        $ip = IP::fromProtocol('2001:db8::a60:8a2e:370:7334');
        $this->assertSame($ip->offset(1)->getBinary(), $ip->next()->getBinary());
        $this->assertSame($ip->offset(-1)->getBinary(), $ip->previous()->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getOffsetOverflowValues()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getOffsetOverflowValues')]
    public function testOffsetThrowsExceptionOnOverflow(string $start, int $offset): void
    {
        $ip = IP::fromProtocol($start);
        $this->expectException(OverflowException::class);
        $ip->offset($offset);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidInRangeIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidInRangeIpAddresses')]
    public function testInRange(string $first, string $second, int $cidr): void
    {
        $first = IP::fromProtocol($first);
        $second = IP::fromProtocol($second);
        $this->assertTrue($first->inRange($second, $cidr));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testDifferentVersionsAreNotInRange(): void
    {
        $ip = IP::fromProtocol('::12.34.56.78');
        $other = IPv4::fromProtocol('12.34.56.78');
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
        $first = IP::fromProtocol($first);
        $second = IP::fromProtocol($second);
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
        $first = IP::fromProtocol('2001:db8::a60:8a2e:370:7334');
        $second = IPv4::fromProtocol('12.34.56.78');
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
        $this->assertSame($isCompatible, $ip->isCompatible());
    }

    /**
     * @test
     * @deprecated Retains coverage of the deprecated IpInterface::isEmbedded() on non-Multi classes.
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testIsEmbeddedAlwaysReturnsFalse(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
        $this->assertSame($isUnicastGlobal, $ip->isUnicastGlobal());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testStringCasting(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($compacted, (string) $ip);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testToStringReturnsCanonicalNotation(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($compacted, $ip->toString());
        $this->assertSame((string) $ip, $ip->toString());
        $this->assertSame($ip->getBinary(), IP::fromProtocol($ip->toString())->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testJsonSerializesToCanonicalNotation(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertInstanceOf(\JsonSerializable::class, $ip);
        $this->assertSame($ip->toString(), $ip->jsonSerialize());
        $this->assertSame(\json_encode($ip->toString()), \json_encode($ip));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getOctetAddresses()
     * @param list<int<0, 255>> $expectedOctets
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getOctetAddresses')]
    public function testGetOctets(string $value, array $expectedOctets): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($expectedOctets, $ip->getOctets());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getSegmentAddresses()
     * @param list<int<0, 65535>> $expectedSegments
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getSegmentAddresses')]
    public function testGetSegments(string $value, array $expectedSegments): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($expectedSegments, $ip->getSegments());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testPerCallFormatterOverridesGlobal(): void
    {
        $ip = IP::fromProtocol('2001:db8::a60:8a2e:370:7334');
        $this->assertSame(StubFormatter::SENTINEL, $ip->getCompactedAddress(new StubFormatter()));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testPerCallNativeFormatterProducesNativeOutput(): void
    {
        $ip = IP::fromProtocol('::ffff:c22:384e');
        $this->assertSame('::ffff:c22:384e', $ip->getCompactedAddress());
        $this->assertSame('::ffff:12.34.56.78', $ip->getCompactedAddress(new NativeFormatter()));
        $this->assertSame('::ffff:c22:384e', $ip->getCompactedAddress());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testExplicitNullPerCallFormatterFallsBackToGlobal(): void
    {
        $ip = IP::fromProtocol('::ffff:c22:384e');
        $this->assertSame('::ffff:c22:384e', $ip->getCompactedAddress(null));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testInvalidPerCallFormatterTriggersDeprecationAndFallsBack(): void
    {
        $ip = IP::fromProtocol('2001:db8::a60:8a2e:370:7334');
        $result = null;
        $message = $this->captureDeprecation(static function () use ($ip, &$result): void {
            $result = $ip->getCompactedAddress(new \stdClass());
        });
        $this->assertNotNull($message);
        $this->assertSame('2001:db8::a60:8a2e:370:7334', $result);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testFromProtocolAcceptsProtocolNotation(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertInstanceOf(Version6Interface::class, $ip);
        $this->assertSame($hex, Binary::toHex($ip->getBinary()));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidBinarySequences')]
    public function testFromProtocolRejectsRawBinarySequences(string $value, string $hex, string $expanded, string $compacted): void
    {
        $this->expectException(InvalidIpAddressException::class);
        IP::fromProtocol($value);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getInvalidIpAddresses')]
    public function testFromProtocolThrowsOnInvalidAddresses(string $value): void
    {
        $this->expectException(InvalidIpAddressException::class);
        IP::fromProtocol($value);
    }

    /**
     * @test
     * @deprecated Deliberately contrasts the deprecated factory() against fromProtocol().
     */
    #[PHPUnit\Test]
    public function testFromProtocolRejectsWhatFactoryAcceptsAsBinary(): void
    {
        $this->assertInstanceOf(Version6Interface::class, IP::factory('1234567890123456'));
        $this->expectException(InvalidIpAddressException::class);
        IP::fromProtocol('1234567890123456');
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidBinarySequences')]
    public function testFromBinaryAcceptsRawBinarySequences(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::fromBinary($value);
        $this->assertInstanceOf(Version6Interface::class, $ip);
        $this->assertSame($value, $ip->getBinary());
        $this->assertSame($expanded, $ip->getExpandedAddress());
        $this->assertSame($compacted, $ip->getCompactedAddress());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testFromBinaryThrowsOnWrongLength(): void
    {
        $this->expectException(InvalidBinaryException::class);
        try {
            IP::fromBinary('abc');
        } catch (InvalidBinaryException $e) {
            $this->assertSame('abc', $e->getSuppliedIp());
            throw $e;
        }
        $this->fail();
    }

    /** @test */
    #[PHPUnit\Test]
    public function testFromBinaryThrowsOnFourByteSequence(): void
    {
        $this->expectException(InvalidBinaryException::class);
        IP::fromBinary('abcd');
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidBinarySequences')]
    public function testFromHexRoundTripsWithBinary(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::fromHex($hex);
        $this->assertSame($value, $ip->getBinary());
        $this->assertSame($hex, Binary::toHex($ip->getBinary()));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testFromHexIsCaseInsensitive(): void
    {
        $lower = '00000000000000000000000000000001';
        $this->assertSame(IP::fromHex($lower)->getBinary(), IP::fromHex(\strtoupper($lower))->getBinary());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testFromHexThrowsOnNonHexadecimal(): void
    {
        $this->expectException(InvalidIpAddressException::class);
        IP::fromHex('zz000000000000000000000000000000');
    }

    /** @test */
    #[PHPUnit\Test]
    public function testFromHexThrowsOnWrongWidth(): void
    {
        $this->expectException(InvalidBinaryException::class);
        IP::fromHex('0000000000000001');
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testTryFromProtocolReturnsInstanceForValid(string $value, string $hex, string $expanded, string $compacted): void
    {
        $this->assertInstanceOf(Version6Interface::class, IP::tryFromProtocol($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidBinarySequences')]
    public function testTryFromProtocolReturnsNullForRawBinary(string $value, string $hex, string $expanded, string $compacted): void
    {
        $this->assertNull(IP::tryFromProtocol($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidBinarySequences')]
    public function testTryFromBinaryReturnsInstanceForValid(string $value, string $hex, string $expanded, string $compacted): void
    {
        $this->assertInstanceOf(Version6Interface::class, IP::tryFromBinary($value));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testTryFromBinaryReturnsNullForWrongLength(): void
    {
        $this->assertNull(IP::tryFromBinary('abc'));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testTryFromHexReturnsNullForInvalid(): void
    {
        $this->assertNull(IP::tryFromHex('zzzz'));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testIsValidReturnsTrueForProtocolNotation(string $value, string $hex, string $expanded, string $compacted): void
    {
        $this->assertTrue(IP::isValid($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidBinarySequences')]
    public function testIsValidReturnsFalseForRawBinary(string $value, string $hex, string $expanded, string $compacted): void
    {
        $this->assertFalse(IP::isValid($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getInvalidIpAddresses')]
    public function testIsValidReturnsFalseForInvalid(string $value): void
    {
        $this->assertFalse(IP::isValid($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidBinarySequences')]
    public function testToIntegerString(string $value, string $hex, string $expanded, string $compacted): void
    {
        $this->assertSame(Binary::toDecimalString($value), IP::fromBinary($value)->toIntegerString());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testToIntegerStringOfKnownValue(): void
    {
        $this->assertSame('18446744073709551616', IP::fromHex('00000000000000010000000000000000')->toIntegerString());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidBinarySequences')]
    public function testFromIntegerStringRoundTrips(string $value, string $hex, string $expanded, string $compacted): void
    {
        $this->assertSame($value, IP::fromIntegerString(Binary::toDecimalString($value))->getBinary());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testFromIntegerStringZero(): void
    {
        $this->assertSame(\str_repeat("\x00", 16), IP::fromIntegerString('0')->getBinary());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testFromIntegerStringMax(): void
    {
        $this->assertSame(\str_repeat("\xff", 16), IP::fromIntegerString('340282366920938463463374607431768211455')->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getInvalidIntegerStrings()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getInvalidIntegerStrings')]
    public function testFromIntegerStringThrowsOnInvalidInput(string $value): void
    {
        $this->expectException(InvalidIpAddressException::class);
        $this->legacyExpectExceptionMessage('The IP address supplied is not valid.');
        try {
            IP::fromIntegerString($value);
        } catch (InvalidIpAddressException $e) {
            $this->assertSame($value, $e->getSuppliedIp());
            throw $e;
        }
        $this->fail();
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidBinarySequences')]
    public function testToHexString(string $value, string $hex, string $expanded, string $compacted): void
    {
        $ip = IP::fromBinary($value);
        $this->assertSame($hex, $ip->toHexString());
        $this->assertSame(32, \strlen($ip->toHexString()));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidBinarySequences')]
    public function testToHexStringRoundTripsWithFromHex(string $value, string $hex, string $expanded, string $compacted): void
    {
        $this->assertSame($value, IP::fromHex(IP::fromBinary($value)->toHexString())->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getMappedIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getMappedIpAddresses')]
    public function testIsEmbeddedAccordingToStrategy(string $value, bool $isMapped): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($isMapped, $ip->isEmbeddedAccordingToStrategy(new Mapped()));
        $this->assertSame($ip->isMapped(), $ip->isEmbeddedAccordingToStrategy(new Mapped()));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getValidIpAddresses')]
    public function testIsNat64WellKnown(string $value, bool $embedded): void
    {
        $this->assertSame($embedded, IP::fromBinary($value)->isNat64WellKnown());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getLocalUseSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getLocalUseSequences')]
    public function testIsNat64LocalUse(string $value, string $embedded): void
    {
        $this->assertTrue(IP::fromBinary($value)->isNat64LocalUse());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getNonMatchingLocalUseSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getNonMatchingLocalUseSequences')]
    public function testIsNat64LocalUseReturnsFalseOutsidePrefix(string $value): void
    {
        $this->assertFalse(IP::fromBinary($value)->isNat64LocalUse());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Teredo::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(TeredoDataProvider::class, 'getValidIpAddresses')]
    public function testIsTeredo(string $value, bool $embedded): void
    {
        $this->assertSame($embedded, IP::fromBinary($value)->isTeredo());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testGetEmbeddedIpWithDefaultStrategy(): void
    {
        $embedded = IP::fromProtocol('::ffff:12.34.56.78')->getEmbeddedIp();
        $this->assertInstanceOf(IPv4::class, $embedded);
        $this->assertSame('12.34.56.78', $embedded->getDotAddress());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testGetEmbeddedIpThrowsWhenNotEmbedded(): void
    {
        $ip = IP::fromProtocol('2001:db8::1');
        $this->expectException(WrongVersionException::class);
        $ip->getEmbeddedIp();
    }

    /** @test */
    #[PHPUnit\Test]
    public function testGetEmbeddedIpWithExplicitStrategy(): void
    {
        $ip = IP::fromProtocol('2002:c22:384e::');
        $this->assertSame('12.34.56.78', $ip->getEmbeddedIp(new Derived())->getDotAddress());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testGetEmbeddedIpThrowsWhenNotEmbeddedAccordingToDefaultStrategy(): void
    {
        // A 6to4-derived form embeds nothing according to the Mapped default.
        $ip = IP::fromProtocol('2002:c22:384e::');
        $this->expectException(WrongVersionException::class);
        $ip->getEmbeddedIp();
    }

    /** @test */
    #[PHPUnit\Test]
    public function testGetEmbeddedIpRoundTripsWithFromEmbedded(): void
    {
        $this->assertSame(
            IPv4::fromProtocol('12.34.56.78')->getBinary(),
            IP::fromEmbedded('12.34.56.78', new Mapped())->getEmbeddedIp(new Mapped())->getBinary()
        );
    }
}
