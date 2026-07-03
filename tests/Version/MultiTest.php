<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\Version;

use Darsyn\IP\Contracts\ArithmeticInterface;
use Darsyn\IP\Contracts\Classification4Interface;
use Darsyn\IP\Contracts\Classification6Interface;
use Darsyn\IP\Contracts\ClassificationInterface;
use Darsyn\IP\Contracts\ComparisonInterface;
use Darsyn\IP\Contracts\Factory4Interface;
use Darsyn\IP\Contracts\FactoryInterface;
use Darsyn\IP\Contracts\Output4Interface;
use Darsyn\IP\Contracts\Output6Interface;
use Darsyn\IP\Contracts\OutputInterface;
use Darsyn\IP\Contracts\StrategyDetectionInterface;
use Darsyn\IP\Contracts\VersionIdentityInterface;
use Darsyn\IP\Exception\InvalidBinaryException;
use Darsyn\IP\Exception\InvalidIpAddressException;
use Darsyn\IP\Exception\OverflowException;
use Darsyn\IP\Exception\WrongVersionException;
use Darsyn\IP\Formatter\ConsistentFormatter;
use Darsyn\IP\IpInterface;
use Darsyn\IP\Strategy;
use Darsyn\IP\Tests\DataProvider\IPv4 as IPv4DataProvider;
use Darsyn\IP\Tests\DataProvider\IPv6 as IPv6DataProvider;
use Darsyn\IP\Tests\DataProvider\Multi as MultiDataProvider;
use Darsyn\IP\Tests\DataProvider\Strategy\Nat64 as Nat64DataProvider;
use Darsyn\IP\Tests\DataProvider\Strategy\Teredo as TeredoDataProvider;
use Darsyn\IP\Tests\Stub\StubFormatter;
use Darsyn\IP\Tests\TestCase;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Version\IPv4;
use Darsyn\IP\Version\IPv6;
use Darsyn\IP\Version\Multi as IP;
use Darsyn\IP\Version\MultiVersionInterface;
use Darsyn\IP\Version\Version4Interface;
use Darsyn\IP\Version\Version6Interface;
use PHPUnit\Framework\Attributes as PHPUnit;

class MultiTest extends TestCase
{
    /** @before */
    #[PHPUnit\Before]
    public function resetDefaultEmbeddingStrategy(): void
    {
        IP::setDefaultEmbeddingStrategy(new Strategy\Mapped());
    }

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
        $ip = IP::fromProtocol('127.0.0.1');
        $this->assertInstanceOf(VersionIdentityInterface::class, $ip);
        $this->assertInstanceOf(ComparisonInterface::class, $ip);
        $this->assertInstanceOf(ArithmeticInterface::class, $ip);
        $this->assertInstanceOf(OutputInterface::class, $ip);
        $this->assertInstanceOf(Output4Interface::class, $ip);
        $this->assertInstanceOf(Output6Interface::class, $ip);
        $this->assertInstanceOf(ClassificationInterface::class, $ip);
        $this->assertInstanceOf(Classification4Interface::class, $ip);
        $this->assertInstanceOf(Classification6Interface::class, $ip);
        $this->assertInstanceOf(FactoryInterface::class, $ip);
        $this->assertInstanceOf(Factory4Interface::class, $ip);
        $this->assertInstanceOf(StrategyDetectionInterface::class, $ip);
        $this->assertInstanceOf(MultiVersionInterface::class, $ip);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidProtocolIpAddresses')]
    public function testInstantiationWithValidAddresses(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertInstanceOf(IpInterface::class, $ip);
        $this->assertInstanceOf(Version4Interface::class, $ip);
        $this->assertInstanceOf(Version6Interface::class, $ip);
        $this->assertInstanceOf(MultiVersionInterface::class, $ip);
    }

    /**
     * @test
     * @deprecated Retains coverage of the deprecated factory() with an explicit embedding strategy.
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getEmbeddingStrategyIpAddresses()
     * @param class-string<Strategy\EmbeddingStrategyInterface> $strategyClass
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getEmbeddingStrategyIpAddresses')]
    public function testEmbeddingStrategy(string $strategyClass, string $expandedAddress, string $v4address): void
    {
        $ip = IP::factory($v4address, new $strategyClass());
        $this->assertSame($expandedAddress, $ip->toExpandedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getEmbeddingStrategyIpAddresses()
     * @param class-string<Strategy\EmbeddingStrategyInterface> $strategyClass
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getEmbeddingStrategyIpAddresses')]
    public function testDefaufltEmbeddingStrategy(string $strategyClass, string $expandedAddress, string $v4address): void
    {
        IP::setDefaultEmbeddingStrategy(new $strategyClass());
        $ip = IP::fromProtocol($v4address);
        $this->assertSame($expandedAddress, $ip->toExpandedAddress());
    }

    /**
     * @test
     * @deprecated Retains coverage of the deprecated factory() raw-binary path.
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidBinarySequences')]
    public function testBinarySequenceIsTheSameOnceInstantiated(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $ip = IP::factory($value);
        $this->assertSame($value, $ip->getBinary());
    }

    /**
     * @test
     * @deprecated Retains coverage of the deprecated factory() protocol path.
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidProtocolIpAddresses')]
    public function testProtocolNotationConvertsToCorrectBinarySequence(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $ip = IP::factory($value);
        $actualHex = \unpack('H*hex', $ip->getBinary());
        $this->assertSame($hex, \is_array($actualHex) ? $actualHex['hex'] : null);
    }

    /**
     * @test
     * @deprecated Retains coverage of the deprecated factory() validation path.
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getInvalidIpAddresses')]
    public function testExceptionIsThrownOnInstantiationWithInvalidAddresses(string $value): void
    {
        $this->expectException(InvalidIpAddressException::class);
        $this->legacyExpectExceptionMessage('The IP address supplied is not valid.');
        try {
            $ip = IP::factory($value);
        } catch (InvalidIpAddressException $e) {
            $this->assertSame($value, $e->getSuppliedIp());
            throw $e;
        }
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidProtocolIpAddresses')]
    public function testGetBinaryAlwaysReturnsA16ByteString(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame(16, \strlen(\bin2hex($ip->getBinary())) / 2);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidProtocolIpAddresses')]
    public function testCompactedAddressReturnsCorrectString(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($compacted, $ip->toCompactedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidProtocolIpAddresses')]
    public function testExpandedAddressReturnsCorrectString(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($expanded, $ip->toExpandedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpVersion4Addresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidProtocolIpVersion4Addresses')]
    public function testDotAddressReturnsCorrectString(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($dot, $ip->toDotAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpVersion6Addresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidProtocolIpVersion6Addresses')]
    public function testDotAddressThrowsExceptionForNonVersion4Addresses(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $this->expectException(\Darsyn\IP\Exception\WrongVersionException::class);
        try {
            $ip = IP::fromProtocol($value);
            $ip->toDotAddress();
        } catch (WrongVersionException $e) {
            $this->assertTrue(isset($ip));
            $this->assertSame((string) $ip, $e->getSuppliedIp());
            $this->assertSame(4, $e->getExpectedVersion());
            $this->assertSame(6, $e->getActualVersion());
            throw $e;
        }
    }

    /**
     * @test
     * @deprecated
     */
    #[PHPUnit\Test]
    public function testGetDotAddressRemainsADeprecatedAliasForwardingTheFormatter(): void
    {
        $ip = IP::fromProtocol('12.34.56.78');
        $this->assertSame($ip->toDotAddress(), $ip->getDotAddress());
        $formatter = new StubFormatter();
        $this->assertSame($ip->toDotAddress($formatter), $ip->getDotAddress($formatter));
    }

    /**
     * @test
     * @deprecated
     */
    #[PHPUnit\Test]
    public function testGetProtocolAppropriateAddressRemainsADeprecatedAliasForwardingTheFormatter(): void
    {
        $ip = IP::fromProtocol('2001:db8::1');
        $this->assertSame($ip->toProtocolAppropriateAddress(), $ip->getProtocolAppropriateAddress());
        $formatter = new StubFormatter();
        $this->assertSame($ip->toProtocolAppropriateAddress($formatter), $ip->getProtocolAppropriateAddress($formatter));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getProtocolIpAddressVersions()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getProtocolIpAddressVersions')]
    public function testVersion(string $value, int $version): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($version, $ip->getVersion());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getNetworkIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getNetworkIpAddresses')]
    public function testNetworkIp(string $initial, string $expected, int $cidr): void
    {
        $ip = IP::fromProtocol($initial);
        $this->assertSame($expected, $ip->getNetworkIp($cidr)->toProtocolAppropriateAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getBroadcastIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getBroadcastIpAddresses')]
    public function testBroadcastIp(string $initial, string $expected, int $cidr): void
    {
        $ip = IP::fromProtocol($initial);
        $this->assertSame($expected, $ip->getBroadcastIp($cidr)->toProtocolAppropriateAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getOffsetAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getOffsetAddresses')]
    public function testOffset(string $start, int $offset, string $expected): void
    {
        $result = IP::fromProtocol($start)->offset($offset);
        $this->assertInstanceOf(IP::class, $result);
        $this->assertSame($expected, $result->toProtocolAppropriateAddress());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testNextAndPreviousAreOffsetByOne(): void
    {
        $ip = IP::fromProtocol('12.34.56.78');
        $this->assertSame($ip->offset(1)->getBinary(), $ip->next()->getBinary());
        $this->assertSame($ip->offset(-1)->getBinary(), $ip->previous()->getBinary());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testEmbeddedOffsetPreservesNonEmbeddedBits(): void
    {
        // A non-canonical 6to4 address: embedded IPv4 192.0.2.1 with a non-zero
        // interface ID. Stepping must re-pack non-canonically (bits 48-127 kept).
        $ip = IP::fromBinary(Binary::fromHex('2002c00002010000dead00000000beef'), new Strategy\Derived());
        $stepped = $ip->offset(1);
        $this->assertSame('2002c00002020000dead00000000beef', Binary::toHex($stepped->getBinary()));
        // A naive step of the raw 16-byte sequence would instead alter the
        // interface ID and leave the embedded IPv4 address untouched.
        $this->assertNotSame(Binary::increment($ip->getBinary()), $stepped->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getOffsetOverflowValues()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getOffsetOverflowValues')]
    public function testOffsetThrowsExceptionOnOverflow(string $start, int $offset): void
    {
        $ip = IP::fromProtocol($start);
        $this->expectException(OverflowException::class);
        $ip->offset($offset);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidInRangeIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidInRangeIpAddresses')]
    public function testInRange(string $first, string $second, int $cidr): void
    {
        $first = IP::fromProtocol($first);
        $second = IP::fromProtocol($second);
        $this->assertTrue($first->inRange($second, $cidr));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testDifferentVersionsAreInRange(): void
    {
        $first = IP::fromProtocol('127.0.0.1', new Strategy\Mapped());
        $second = IPv6::fromProtocol('::1234:5678:abcd:90ef');
        $this->assertTrue($first->inRange($second, 0));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testDifferentByteLengthsAreNotInRange(): void
    {
        $first = IP::fromProtocol('127.0.0.1');
        $second = IPv4::fromProtocol('127.0.0.1');
        $this->expectException(WrongVersionException::class);
        $first->inRange($second, 0);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getCommonCidrValues()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getCommonCidrValues')]
    public function testCommonCidr(string $first, string $second, int $expectedCidr): void
    {
        $first = IP::fromProtocol($first);
        $second = IP::fromProtocol($second);
        $this->assertSame($expectedCidr, $first->getCommonCidr($second));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testCommonCidrThrowsException(): void
    {
        $first = IP::fromProtocol('12.34.56.78');
        $second = IPv4::fromProtocol('12.34.56.78');
        $this->expectException(WrongVersionException::class);
        $first->getCommonCidr($second);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getLinkLocalIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getLinkLocalIpAddresses')]
    public function testIsLinkLocal(string $value, bool $isLinkLocal): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($isLinkLocal, $ip->isLinkLocal());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getMappedLoopbackIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getMappedLoopbackIpAddresses')]
    public function testIsLoopbackMapped(string $value, bool $isLoopback): void
    {
        $ip = IP::fromProtocol($value, new Strategy\Mapped());
        $this->assertSame($isLoopback, $ip->isLoopback());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getCompatibleLoopbackIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getCompatibleLoopbackIpAddresses')]
    public function testIsLoopbackCompatible(string $value, bool $isLoopback): void
    {
        $ip = IP::fromProtocol($value, new Strategy\Compatible());
        if ('0000:0000:0000:0000:0000:0000:0000:0001' === $ip->toExpandedAddress()) {
            // Special case that I can't figure out a solution for.
            // The address 0.0.0.1 (when using the compatible embedding strategy)
            // is a loopback address if viewing as IPv6 (::1), but also not a
            // loopback address (127.x.x.x) if viewing as an IPv4-embedded address.
            $this->markTestSkipped();
        }
        $this->assertSame($isLoopback, $ip->isLoopback());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getDerivedLoopbackIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getDerivedLoopbackIpAddresses')]
    public function testIsLoopbackDerived(string $value, bool $isLoopback): void
    {
        $ip = IP::fromProtocol($value, new Strategy\Derived());
        $this->assertSame($isLoopback, $ip->isLoopback());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getTeredoLoopbackIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getTeredoLoopbackIpAddresses')]
    public function testIsLoopbackTeredo(string $value, bool $isLoopback): void
    {
        $ip = IP::fromProtocol($value, new Strategy\Teredo());
        $this->assertSame($isLoopback, $ip->isLoopback());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getMulticastIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getMulticastIpAddresses')]
    public function testIsMulticast(string $value, bool $isMulticast): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($isMulticast, $ip->isMulticast());

    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getPrivateUseIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getPrivateUseIpAddresses')]
    public function testIsPrivateUse(string $value, bool $isPrivateUse): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($isPrivateUse, $ip->isPrivateUse());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getUnspecifiedIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getUnspecifiedIpAddresses')]
    public function testIsUnspecified(string $value, bool $isUnspecified): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($isUnspecified, $ip->isUnspecified());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getBenchmarkingIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getBenchmarkingIpAddresses')]
    public function testIsBenchmarking(string $value, bool $isBenchmarking): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($isBenchmarking, $ip->isBenchmarking());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getDocumentationIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getDocumentationIpAddresses')]
    public function testIsDocumentation(string $value, bool $isDocumentation): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($isDocumentation, $ip->isDocumentation());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getGloballyReachableIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getGloballyReachableIpAddresses')]
    public function testIsGloballyReachable(string $value, bool $isGloballyReachable): void
    {
        $ip = IP::fromProtocol($value, new Strategy\Mapped());
        $this->assertSame($isGloballyReachable, $ip->isGloballyReachable());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getUniqueLocalIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getUniqueLocalIpAddresses')]
    public function testIsUniqueLocal(string $value, bool $isUniqueLocal, bool $willThrowException): void
    {
        $ip = IP::fromProtocol($value, new Strategy\Mapped());
        $willThrowException && $this->expectException(WrongVersionException::class);
        $this->assertSame($isUniqueLocal, $ip->isUniqueLocal());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getUnicastIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getUnicastIpAddresses')]
    public function testIsUnicast(string $value, bool $isUnicast, bool $willThrowException): void
    {
        $ip = IP::fromProtocol($value, new Strategy\Mapped());
        $willThrowException && $this->expectException(WrongVersionException::class);
        $this->assertSame($isUnicast, $ip->isUnicast());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getUnicastGlobalIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getUnicastGlobalIpAddresses')]
    public function testIsUnicastGlobal(string $value, bool $isUnicastGlobal, bool $willThrowException): void
    {
        $ip = IP::fromProtocol($value, new Strategy\Mapped());
        $willThrowException && $this->expectException(WrongVersionException::class);
        $this->assertSame($isUnicastGlobal, $ip->isUnicastGlobal());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getIsBroadcastIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getIsBroadcastIpAddresses')]
    public function testIsBroadcast(string $value, bool $isBroadcast, bool $willThrowException): void
    {
        $ip = IP::fromProtocol($value);
        $willThrowException && $this->expectException(WrongVersionException::class);
        $this->assertSame($isBroadcast, $ip->isBroadcast());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getSharedIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getSharedIpAddresses')]
    public function testIsShared(string $value, bool $isShared, bool $willThrowException): void
    {
        $ip = IP::fromProtocol($value);
        $willThrowException && $this->expectException(WrongVersionException::class);
        $this->assertSame($isShared, $ip->isShared());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getFutureReservedIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getFutureReservedIpAddresses')]
    public function testIsFutureReserved(string $value, bool $isFutureReserved, bool $willThrowException): void
    {
        $ip = IP::fromProtocol($value);
        $willThrowException && $this->expectException(WrongVersionException::class);
        $this->assertSame($isFutureReserved, $ip->isFutureReserved());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidProtocolIpAddresses')]
    public function testStringCasting(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $ip = IP::fromProtocol($value);
        null !== $dot
            ? $this->assertSame($dot, (string) $ip)
            : $this->assertSame($compacted, (string) $ip);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidProtocolIpAddresses')]
    public function testToStringReturnsProtocolAppropriateNotation(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($dot ?? $compacted, $ip->toString());
        $this->assertSame((string) $ip, $ip->toString());
        $this->assertSame($ip->getBinary(), IP::fromProtocol($ip->toString())->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidProtocolIpAddresses')]
    public function testJsonSerializesToProtocolAppropriateNotation(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertInstanceOf(\JsonSerializable::class, $ip);
        $this->assertSame($ip->toString(), $ip->jsonSerialize());
        $this->assertSame(\json_encode($ip->toString()), \json_encode($ip));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getOctetAddresses()
     * @param list<int<0, 255>> $expectedOctets
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getOctetAddresses')]
    public function testGetOctets(string $value, array $expectedOctets): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($expectedOctets, $ip->getOctets());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getSegmentAddresses()
     * @param list<int<0, 65535>> $expectedSegments
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getSegmentAddresses')]
    public function testGetSegmentsForNonEmbeddedAddress(string $value, array $expectedSegments): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($expectedSegments, $ip->getSegments());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testGetSegmentsThrowsExceptionForEmbeddedAddress(): void
    {
        $ip = IP::fromProtocol('119.14.113.44');
        $this->expectException(WrongVersionException::class);
        $ip->getSegments();
    }

    /** @test */
    #[PHPUnit\Test]
    public function testGetOctetsForEmbeddedAddressWithNonDefaultStrategy(): void
    {
        $ip = IP::fromProtocol('12.34.56.78', new Strategy\Derived());
        $this->assertSame([12, 34, 56, 78], $ip->getOctets());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testGetSegmentsThrowsExceptionForEmbeddedAddressWithNonDefaultStrategy(): void
    {
        $ip = IP::fromProtocol('12.34.56.78', new Strategy\Derived());
        $this->expectException(WrongVersionException::class);
        $ip->getSegments();
    }

    /** @test */
    #[PHPUnit\Test]
    public function testPerCallFormatterOverridesGlobalForProtocolAppropriateAddress(): void
    {
        $ip = IP::fromProtocol('12.34.56.78');
        $this->assertSame(StubFormatter::SENTINEL, $ip->toProtocolAppropriateAddress(new StubFormatter()));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testPerCallFormatterDoesNotMutateGlobalForProtocolAppropriateAddress(): void
    {
        $ip = IP::fromProtocol('12.34.56.78');
        $this->assertSame(StubFormatter::SENTINEL, $ip->toProtocolAppropriateAddress(new StubFormatter()));
        $this->assertSame('12.34.56.78', $ip->toProtocolAppropriateAddress());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testExplicitNullPerCallFormatterFallsBackToGlobalForProtocolAppropriateAddress(): void
    {
        $ip = IP::fromProtocol('12.34.56.78');
        $this->assertSame('12.34.56.78', $ip->toProtocolAppropriateAddress(null));
    }

    /**
     * @test
     * @deprecated
     */
    #[PHPUnit\Test]
    public function testInvalidPerCallFormatterTriggersDeprecationForProtocolAppropriateAddress(): void
    {
        $ip = IP::fromProtocol('12.34.56.78');
        $result = null;
        $message = $this->captureDeprecation(static function () use ($ip, &$result): void {
            $result = $ip->getProtocolAppropriateAddress(new \stdClass());
        });
        $this->assertNotNull($message);
        $this->assertSame('12.34.56.78', $result);
    }

    /** @test */
    #[PHPUnit\Test]
    public function testPerCallFormatterOverridesGlobalForDotAddress(): void
    {
        $ip = IP::fromProtocol('12.34.56.78');
        $this->assertSame(StubFormatter::SENTINEL, $ip->toDotAddress(new StubFormatter()));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testPerCallFormatterDoesNotMutateGlobalForDotAddress(): void
    {
        $ip = IP::fromProtocol('12.34.56.78');
        $this->assertSame(StubFormatter::SENTINEL, $ip->toDotAddress(new StubFormatter()));
        $this->assertSame('12.34.56.78', $ip->toDotAddress());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testExplicitNullPerCallFormatterFallsBackToGlobalForDotAddress(): void
    {
        $ip = IP::fromProtocol('12.34.56.78');
        $this->assertSame('12.34.56.78', $ip->toDotAddress(null));
    }

    /**
     * @test
     * @deprecated
     */
    #[PHPUnit\Test]
    public function testInvalidPerCallFormatterTriggersDeprecationForDotAddress(): void
    {
        $ip = IP::fromProtocol('12.34.56.78');
        $result = null;
        $message = $this->captureDeprecation(static function () use ($ip, &$result): void {
            $result = $ip->getDotAddress(new \stdClass());
        });
        $this->assertNotNull($message);
        $this->assertSame('12.34.56.78', $result);
    }

    /**
     * @test
     * @deprecated
     */
    #[PHPUnit\Test]
    public function testInvalidPerCallFormatterTriggersDeprecationForDotAddressOnNonEmbedded(): void
    {
        // The formatter argument is validated before the version check, so the
        // deprecation fires even though the IPv6 address ultimately rejects
        // dotted notation with a WrongVersionException.
        $ip = IP::fromProtocol('2001:db8::1');
        $thrown = null;
        $message = $this->captureDeprecation(static function () use ($ip, &$thrown): void {
            try {
                $ip->getDotAddress(new \stdClass());
            } catch (WrongVersionException $e) {
                $thrown = $e;
            }
        });
        $this->assertNotNull($message);
        $this->assertInstanceOf(WrongVersionException::class, $thrown);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidProtocolIpAddresses')]
    public function testFromProtocolAcceptsProtocolNotation(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertInstanceOf(MultiVersionInterface::class, $ip);
        $this->assertSame($hex, Binary::toHex($ip->getBinary()));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidBinarySequences')]
    public function testFromProtocolRejectsRawBinarySequences(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $this->expectException(InvalidIpAddressException::class);
        IP::fromProtocol($value);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getInvalidIpAddresses')]
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
        $this->assertInstanceOf(MultiVersionInterface::class, IP::factory('1234567890123456'));
        $this->expectException(InvalidIpAddressException::class);
        IP::fromProtocol('1234567890123456');
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidBinarySequences')]
    public function testFromBinaryAcceptsRawBinarySequences(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $ip = IP::fromBinary($value);
        $this->assertInstanceOf(MultiVersionInterface::class, $ip);
        $this->assertSame($value, $ip->getBinary());
        $this->assertSame($expanded, $ip->toExpandedAddress());
        $this->assertSame($compacted, $ip->toCompactedAddress());
        if (null !== $dot) {
            $this->assertSame($dot, $ip->toDotAddress());
        }
    }

    /** @test */
    #[PHPUnit\Test]
    public function testFromBinaryPacksFourByteSequenceWithDefaultStrategy(): void
    {
        $ip = IP::fromBinary(Binary::fromHex('0c22384e'));
        $this->assertInstanceOf(MultiVersionInterface::class, $ip);
        $this->assertTrue($ip->isVersion4());
        $this->assertSame('12.34.56.78', $ip->toDotAddress());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testFromBinaryThrowsOnWrongLength(): void
    {
        $this->expectException(InvalidBinaryException::class);
        try {
            IP::fromBinary('abcde');
        } catch (InvalidBinaryException $e) {
            $this->assertSame('abcde', $e->getSuppliedIp());
            throw $e;
        }
        $this->fail();
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getEmbeddingStrategyIpAddresses()
     * @param class-string<Strategy\EmbeddingStrategyInterface> $strategyClass
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getEmbeddingStrategyIpAddresses')]
    public function testFromProtocolUsesExplicitStrategy(string $strategyClass, string $expandedAddress, string $v4address): void
    {
        $ip = IP::fromProtocol($v4address, new $strategyClass());
        $this->assertSame($expandedAddress, $ip->toExpandedAddress());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testFromBinaryUsesExplicitStrategy(): void
    {
        $ip = IP::fromBinary(Binary::fromHex('0c22384e'), new Strategy\Derived());
        $this->assertSame('2002:0c22:384e:0000:0000:0000:0000:0000', $ip->toExpandedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidBinarySequences')]
    public function testFromHexRoundTripsWithBinary(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $ip = IP::fromHex($hex);
        $this->assertSame($value, $ip->getBinary());
        $this->assertSame($hex, Binary::toHex($ip->getBinary()));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testFromHexThrowsOnWrongWidth(): void
    {
        $this->expectException(InvalidBinaryException::class);
        IP::fromHex('0c22384e0c22');
    }

    /** @test */
    #[PHPUnit\Test]
    public function testFromHexThrowsOnNonHexadecimal(): void
    {
        $this->expectException(InvalidIpAddressException::class);
        IP::fromHex('zz000000000000000000000000000000');
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidProtocolIpAddresses')]
    public function testTryFromProtocolReturnsInstanceForValid(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $this->assertInstanceOf(MultiVersionInterface::class, IP::tryFromProtocol($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidBinarySequences')]
    public function testTryFromProtocolReturnsNullForRawBinary(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $this->assertNull(IP::tryFromProtocol($value));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testTryFromBinaryReturnsNullForWrongLength(): void
    {
        $this->assertNull(IP::tryFromBinary('abcde'));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testTryFromHexReturnsNullForInvalid(): void
    {
        $this->assertNull(IP::tryFromHex('zzzz'));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidProtocolIpAddresses')]
    public function testIsValidReturnsTrueForProtocolNotation(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $this->assertTrue(IP::isValid($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getInvalidIpAddresses')]
    public function testIsValidReturnsFalseForInvalid(string $value): void
    {
        $this->assertFalse(IP::isValid($value));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testToIntegerForEmbeddedAddress(): void
    {
        $ip = IP::fromProtocol('12.34.56.78');
        $this->assertSame(203569230, $ip->toInteger());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testToIntegerWithNonDefaultStrategy(): void
    {
        $ip = IP::fromProtocol('12.34.56.78', new Strategy\Derived());
        $this->assertSame(203569230, $ip->toInteger());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testToIntegerThrowsForNonEmbeddedAddress(): void
    {
        $ip = IP::fromProtocol('2001:db8::1');
        $this->expectException(WrongVersionException::class);
        $ip->toInteger();
    }

    /** @test */
    #[PHPUnit\Test]
    public function testFromIntegerUsesDefaultMappedStrategy(): void
    {
        $ip = IP::fromInteger(203569230);
        $this->assertSame('00000000000000000000ffff0c22384e', Binary::toHex($ip->getBinary()));
        $this->assertSame('12.34.56.78', $ip->toProtocolAppropriateAddress());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testFromIntegerUsesExplicitStrategy(): void
    {
        $ip = IP::fromInteger(203569230, new Strategy\Derived());
        $this->assertSame('2002:0c22:384e:0000:0000:0000:0000:0000', $ip->toExpandedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getInvalidIntegers()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getInvalidIntegers')]
    public function testFromIntegerThrowsOnOutOfRange(int $integer): void
    {
        $this->expectException(InvalidIpAddressException::class);
        IP::fromInteger($integer);
    }

    /** @test */
    #[PHPUnit\Test]
    public function testTryFromIntegerReturnsInstanceForValid(): void
    {
        $this->assertInstanceOf(MultiVersionInterface::class, IP::tryFromInteger(203569230));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getInvalidIntegers()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getInvalidIntegers')]
    public function testTryFromIntegerReturnsNullForOutOfRange(int $integer): void
    {
        $this->assertNull(IP::tryFromInteger($integer));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testToHexStringReturnsFullWidthWhenEmbedded(): void
    {
        $ip = IP::fromProtocol('12.34.56.78');
        $this->assertSame(32, \strlen($ip->toHexString()));
        $this->assertSame('00000000000000000000ffff0c22384e', $ip->toHexString());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testToIntegerStringReturnsFullWidthWhenEmbedded(): void
    {
        $ip = IP::fromProtocol('12.34.56.78');
        $this->assertSame('281470885312590', $ip->toIntegerString());
        $this->assertNotSame((string) $ip->toInteger(), $ip->toIntegerString());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidBinarySequences')]
    public function testFromIntegerStringBuildsFullSixteenBytes(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $this->assertSame($value, IP::fromIntegerString(Binary::toDecimalString($value))->getBinary());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testFromIntegerStringThreadsStrategy(): void
    {
        $ip = IP::fromIntegerString('281470885312590', new Strategy\Mapped());
        $this->assertSame('12.34.56.78', $ip->toDotAddress());
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
        IP::fromIntegerString($value);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidBinarySequences')]
    public function testTryFromIntegerStringReturnsInstanceForValid(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $this->assertInstanceOf(MultiVersionInterface::class, IP::tryFromIntegerString(Binary::toDecimalString($value)));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getInvalidIntegerStrings()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getInvalidIntegerStrings')]
    public function testTryFromIntegerStringReturnsNullForInvalid(string $value): void
    {
        $this->assertNull(IP::tryFromIntegerString($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpVersion4Addresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidProtocolIpVersion4Addresses')]
    public function testIsEmbeddedForVersion4Addresses(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $this->assertTrue(IP::fromProtocol($value)->isEmbedded());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpVersion6Addresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidProtocolIpVersion6Addresses')]
    public function testIsEmbeddedForVersion6Addresses(string $value, string $hex, string $expanded, string $compacted, ?string $dot): void
    {
        $this->assertFalse(IP::fromProtocol($value)->isEmbedded());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testGetEmbeddedIpUsesInstanceStrategy(): void
    {
        $ip = IP::fromProtocol('12.34.56.78', new Strategy\Derived());
        $embedded = $ip->getEmbeddedIp();
        $this->assertInstanceOf(IPv4::class, $embedded);
        $this->assertSame('12.34.56.78', $embedded->toDotAddress());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testGetEmbeddedIpWithExplicitStrategyOverridesInstanceStrategy(): void
    {
        $ip = IP::fromProtocol('12.34.56.78', new Strategy\Derived());
        $this->expectException(WrongVersionException::class);
        $ip->getEmbeddedIp(new Strategy\Mapped());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testGetEmbeddedIpThrowsForNonEmbeddedAddress(): void
    {
        $ip = IP::fromProtocol('2001:db8::1');
        $this->expectException(WrongVersionException::class);
        $ip->getEmbeddedIp();
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Teredo::getValidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(TeredoDataProvider::class, 'getValidSequences')]
    public function testGetEmbeddedIpExtractsTeredoClientAddress(string $value, string $embedded): void
    {
        $ip = IP::fromBinary($value, new Strategy\Teredo());
        $this->assertSame($embedded, $ip->getEmbeddedIp()->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getValidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getValidSequences')]
    public function testGetEmbeddedIpExtractsNat64WellKnownAddress(string $value, string $embedded): void
    {
        $ip = IP::fromBinary($value, Strategy\Nat64::wellKnown());
        $this->assertSame($embedded, $ip->getEmbeddedIp()->getBinary());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testGetEmbeddedIpOnIPv6RespectsGlobalDefaultStrategy(): void
    {
        $ip = IPv6::fromProtocol('2002:c22:384e::');
        IP::setDefaultEmbeddingStrategy(new Strategy\Derived());
        $this->assertSame('12.34.56.78', $ip->getEmbeddedIp()->toDotAddress());
        IP::setDefaultEmbeddingStrategy(new Strategy\Mapped());
    }
}
