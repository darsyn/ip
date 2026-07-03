<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\Version;

use Darsyn\IP\Contracts\ArithmeticInterface;
use Darsyn\IP\Contracts\Classification4Interface;
use Darsyn\IP\Contracts\ClassificationInterface;
use Darsyn\IP\Contracts\ComparisonInterface;
use Darsyn\IP\Contracts\Factory4Interface;
use Darsyn\IP\Contracts\FactoryInterface;
use Darsyn\IP\Contracts\Output4Interface;
use Darsyn\IP\Contracts\OutputInterface;
use Darsyn\IP\Contracts\StrategyDetectionInterface;
use Darsyn\IP\Contracts\VersionIdentityInterface;
use Darsyn\IP\Exception\InvalidBinaryException;
use Darsyn\IP\Exception\InvalidCidrException;
use Darsyn\IP\Exception\InvalidIpAddressException;
use Darsyn\IP\Exception\OverflowException;
use Darsyn\IP\Exception\WrongVersionException;
use Darsyn\IP\Formatter\ConsistentFormatter;
use Darsyn\IP\IpInterface;
use Darsyn\IP\Tests\DataProvider\IPv4 as IPv4DataProvider;
use Darsyn\IP\Tests\Stub\StubFormatter;
use Darsyn\IP\Tests\TestCase;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Version\IPv4 as IP;
use Darsyn\IP\Version\IPv6;
use Darsyn\IP\Version\Version4Interface;
use PHPUnit\Framework\Attributes as PHPUnit;

class IPv4Test extends TestCase
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
        $ip = IP::fromProtocol('127.0.0.1');
        $this->assertInstanceOf(VersionIdentityInterface::class, $ip);
        $this->assertInstanceOf(ComparisonInterface::class, $ip);
        $this->assertInstanceOf(ArithmeticInterface::class, $ip);
        $this->assertInstanceOf(OutputInterface::class, $ip);
        $this->assertInstanceOf(Output4Interface::class, $ip);
        $this->assertInstanceOf(ClassificationInterface::class, $ip);
        $this->assertInstanceOf(Classification4Interface::class, $ip);
        $this->assertInstanceOf(FactoryInterface::class, $ip);
        $this->assertInstanceOf(Factory4Interface::class, $ip);
        // Strategy detection is a version 6 concept; IPv4 does not gain it.
        $this->assertNotInstanceOf(StrategyDetectionInterface::class, $ip);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testInstantiationWithValidAddresses(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertInstanceOf(IpInterface::class, $ip);
        $this->assertInstanceOf(Version4Interface::class, $ip);
    }

    /**
     * @test
     * @deprecated Retains coverage of the deprecated factory() raw-binary path.
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
     * @deprecated Retains coverage of the deprecated factory() protocol path.
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
     * @deprecated Retains coverage of the deprecated factory() validation path.
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
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testGetBinaryAlwaysReturnsA4ByteString(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame(4, \strlen(\bin2hex($ip->getBinary())) / 2);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testDotAddressReturnsCorrectString(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($expectedDot, $ip->getDotAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testGetVersionAlwaysReturns4(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame(4, $ip->getVersion());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testIsVersionOnlyReturnsTrueFor4(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertTrue($ip->isVersion(4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testIsVersionOnlyReturnsFalseFor6(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertFalse($ip->isVersion(6));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testIsVersion4AlwaysReturnsTrue(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertTrue($ip->isVersion4());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testIsVersion6AlwaysReturnsFalse(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol('12.34.56.78');
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
        $ip = IP::fromProtocol('12.34.56.78');
        $this->assertSame($expected, $ip->getBroadcastIp($cidr)->getDotAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getOffsetAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getOffsetAddresses')]
    public function testOffset(string $start, int $offset, string $expected): void
    {
        $result = IP::fromProtocol($start)->offset($offset);
        $this->assertInstanceOf(IP::class, $result);
        $this->assertSame($expected, $result->getDotAddress());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testNextAndPreviousAreOffsetByOne(): void
    {
        $ip = IP::fromProtocol('12.34.56.78');
        $this->assertSame($ip->offset(1)->getBinary(), $ip->next()->getBinary());
        $this->assertSame($ip->offset(-1)->getBinary(), $ip->previous()->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getOffsetOverflowValues()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getOffsetOverflowValues')]
    public function testOffsetThrowsExceptionOnOverflow(string $start, int $offset): void
    {
        $ip = IP::fromProtocol($start);
        $this->expectException(OverflowException::class);
        $ip->offset($offset);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidInRangeIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidInRangeIpAddresses')]
    public function testInRange(string $first, string $second, int $cidr): void
    {
        $first = IP::fromProtocol($first);
        $second = IP::fromProtocol($second);
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
        $first = IP::fromProtocol('12.34.56.78');
        $second = IP::fromProtocol('12.34.56.78');
        $this->expectException(InvalidCidrException::class);
        $first->inRange($second, $cidr);
    }

    /** @test */
    #[PHPUnit\Test]
    public function testDifferentVersionsAreNotInRange(): void
    {
        $ip = IP::fromProtocol('12.34.56.78');
        $other = IPv6::fromProtocol('::12.34.56.78');
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
        $first = IP::fromProtocol($first);
        $second = IP::fromProtocol($second);
        $this->assertSame($expectedCidr, $first->getCommonCidr($second));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testCommonCidrThrowsException(): void
    {
        $first = IP::fromProtocol('12.34.56.78');
        $second = IPv6::fromProtocol('2001:db8::a60:8a2e:370:7334');
        $this->expectException(WrongVersionException::class);
        $first->getCommonCidr($second);
    }

    /**
     * @test
     * @deprecated
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testIsMappedAlwaysReturnsFalse(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertFalse($ip->isMapped());
    }

    /**
     * @test
     * @deprecated
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testIsDerivedAlwaysReturnsFalse(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertFalse($ip->isDerived());
    }

    /**
     * @test
     * @deprecated
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testIsCompatibleAlwaysReturnsFalse(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertFalse($ip->isCompatible());
    }

    /**
     * @test
     * @deprecated Retains coverage of the deprecated IpInterface::isEmbedded() on non-Multi classes.
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testIsEmbeddedAlwaysReturnsFalse(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
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
        $ip = IP::fromProtocol($value);
        $this->assertSame($isFutureReserved, $ip->isFutureReserved());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testStringCasting(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($expectedDot, (string) $ip);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testToStringReturnsCanonicalNotation(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($expectedDot, $ip->toString());
        $this->assertSame((string) $ip, $ip->toString());
        $this->assertSame($ip->getBinary(), IP::fromProtocol($ip->toString())->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testJsonSerializesToCanonicalNotation(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertInstanceOf(\JsonSerializable::class, $ip);
        $this->assertSame($ip->toString(), $ip->jsonSerialize());
        $this->assertSame(\json_encode($ip->toString()), \json_encode($ip));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getOctetAddresses()
     * @param list<int<0, 255>> $expectedOctets
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getOctetAddresses')]
    public function testGetOctets(string $value, array $expectedOctets): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($expectedOctets, $ip->getOctets());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testPerCallFormatterOverridesGlobal(): void
    {
        $ip = IP::fromProtocol('12.34.56.78');
        $this->assertSame(StubFormatter::SENTINEL, $ip->getDotAddress(new StubFormatter()));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testPerCallFormatterDoesNotMutateGlobal(): void
    {
        $ip = IP::fromProtocol('12.34.56.78');
        $this->assertSame(StubFormatter::SENTINEL, $ip->getDotAddress(new StubFormatter()));
        $this->assertSame('12.34.56.78', $ip->getDotAddress());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testExplicitNullPerCallFormatterFallsBackToGlobal(): void
    {
        $ip = IP::fromProtocol('12.34.56.78');
        $this->assertSame('12.34.56.78', $ip->getDotAddress(null));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testInvalidPerCallFormatterTriggersDeprecationAndFallsBack(): void
    {
        $ip = IP::fromProtocol('12.34.56.78');
        $result = null;
        $message = $this->captureDeprecation(static function () use ($ip, &$result): void {
            $result = $ip->getDotAddress(new \stdClass());
        });
        $this->assertNotNull($message);
        $this->assertSame('12.34.56.78', $result);
    }

    /** @test */
    #[PHPUnit\Test]
    public function testInvalidScalarPerCallFormatterMentionsTypeInDeprecation(): void
    {
        $ip = IP::fromProtocol('12.34.56.78');
        $message = $this->captureDeprecation(static function () use ($ip): void {
            $ip->getDotAddress(42);
        });
        $this->assertNotNull($message);
        $this->assertStringContainsString('integer', (string) $message);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testFromProtocolAcceptsProtocolNotation(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertInstanceOf(Version4Interface::class, $ip);
        $actualHex = \unpack('H*hex', $ip->getBinary());
        $this->assertSame($expectedHex, \is_array($actualHex) ? $actualHex['hex'] : null);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidBinarySequences')]
    public function testFromProtocolRejectsRawBinarySequences(string $value, string $expectedHex, string $expectedDot): void
    {
        $this->expectException(InvalidIpAddressException::class);
        IP::fromProtocol($value);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getInvalidIpAddresses')]
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
        // factory() permissively turns a raw 4-byte string into an address ...
        $this->assertInstanceOf(Version4Interface::class, IP::factory('abcd'));
        // ... but strict protocol parsing must not (the SSRF footgun this closes).
        $this->expectException(InvalidIpAddressException::class);
        IP::fromProtocol('abcd');
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidBinarySequences')]
    public function testFromBinaryAcceptsRawBinarySequences(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::fromBinary($value);
        $this->assertInstanceOf(Version4Interface::class, $ip);
        $this->assertSame($value, $ip->getBinary());
        $this->assertSame($expectedDot, $ip->getDotAddress());
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
    public function testInvalidBinaryExceptionIsCatchableAsInvalidIpAddress(): void
    {
        try {
            IP::fromBinary('abc');
        } catch (InvalidIpAddressException $e) {
            $this->assertInstanceOf(InvalidBinaryException::class, $e);
            return;
        }
        $this->fail();
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidBinarySequences')]
    public function testFromHexRoundTripsWithBinary(string $value, string $expectedHex, string $expectedDot): void
    {
        $ip = IP::fromHex($expectedHex);
        $this->assertSame($value, $ip->getBinary());
        $this->assertSame($expectedHex, Binary::toHex($ip->getBinary()));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testFromHexIsCaseInsensitive(): void
    {
        $this->assertSame(IP::fromHex('7f000001')->getBinary(), IP::fromHex('7F000001')->getBinary());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testFromHexThrowsOnNonHexadecimal(): void
    {
        $this->expectException(InvalidIpAddressException::class);
        IP::fromHex('zzzzzzzz');
    }

    /** @test */
    #[PHPUnit\Test]
    public function testFromHexThrowsOnWrongWidth(): void
    {
        $this->expectException(InvalidBinaryException::class);
        IP::fromHex('7f0000');
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testTryFromProtocolReturnsInstanceForValid(string $value, string $expectedHex, string $expectedDot): void
    {
        $this->assertInstanceOf(Version4Interface::class, IP::tryFromProtocol($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidBinarySequences')]
    public function testTryFromProtocolReturnsNullForRawBinary(string $value, string $expectedHex, string $expectedDot): void
    {
        $this->assertNull(IP::tryFromProtocol($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidBinarySequences')]
    public function testTryFromBinaryReturnsInstanceForValid(string $value, string $expectedHex, string $expectedDot): void
    {
        $this->assertInstanceOf(Version4Interface::class, IP::tryFromBinary($value));
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
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testIsValidReturnsTrueForProtocolNotation(string $value, string $expectedHex, string $expectedDot): void
    {
        $this->assertTrue(IP::isValid($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getValidBinarySequences')]
    public function testIsValidReturnsFalseForRawBinary(string $value, string $expectedHex, string $expectedDot): void
    {
        $this->assertFalse(IP::isValid($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getInvalidIpAddresses')]
    public function testIsValidReturnsFalseForInvalid(string $value): void
    {
        $this->assertFalse(IP::isValid($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getIntegerAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getIntegerAddresses')]
    public function testToInteger(string $value, int $integer): void
    {
        $this->assertSame($integer, IP::fromProtocol($value)->toInteger());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getIntegerAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getIntegerAddresses')]
    public function testFromInteger(string $value, int $integer): void
    {
        $this->assertSame(IP::fromProtocol($value)->getBinary(), IP::fromInteger($integer)->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getIntegerAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getIntegerAddresses')]
    public function testIntegerRoundTrips(string $value, int $integer): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($integer, IP::fromInteger($ip->toInteger())->toInteger());
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
        $this->legacyExpectExceptionMessage('The IP address supplied is not valid.');
        try {
            IP::fromInteger($integer);
        } catch (InvalidIpAddressException $e) {
            $this->assertSame($integer, $e->getSuppliedIp());
            throw $e;
        }
        $this->fail();
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getIntegerAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getIntegerAddresses')]
    public function testTryFromIntegerReturnsInstanceForValid(string $value, int $integer): void
    {
        $this->assertInstanceOf(Version4Interface::class, IP::tryFromInteger($integer));
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

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getIntegerStringData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getIntegerStringData')]
    public function testToIntegerString(string $value, string $decimal): void
    {
        $this->assertSame($decimal, IP::fromProtocol($value)->toIntegerString());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getIntegerStringData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getIntegerStringData')]
    public function testFromIntegerString(string $value, string $decimal): void
    {
        $this->assertSame(IP::fromProtocol($value)->getBinary(), IP::fromIntegerString($decimal)->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getIntegerStringData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getIntegerStringData')]
    public function testIntegerStringRoundTrips(string $value, string $decimal): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame($decimal, IP::fromIntegerString($ip->toIntegerString())->toIntegerString());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getInvalidIntegerStrings()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getInvalidIntegerStrings')]
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
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getIntegerStringData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getIntegerStringData')]
    public function testTryFromIntegerStringReturnsInstanceForValid(string $value, string $decimal): void
    {
        $this->assertInstanceOf(Version4Interface::class, IP::tryFromIntegerString($decimal));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getInvalidIntegerStrings()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getInvalidIntegerStrings')]
    public function testTryFromIntegerStringReturnsNullForInvalid(string $value): void
    {
        $this->assertNull(IP::tryFromIntegerString($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getHexStringData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getHexStringData')]
    public function testToHexString(string $value, string $hex): void
    {
        $this->assertSame($hex, IP::fromProtocol($value)->toHexString());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv4::getHexStringData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv4DataProvider::class, 'getHexStringData')]
    public function testToHexStringRoundTripsWithFromHex(string $value, string $hex): void
    {
        $ip = IP::fromProtocol($value);
        $this->assertSame(8, \strlen($ip->toHexString()));
        $this->assertSame($ip->getBinary(), IP::fromHex($ip->toHexString())->getBinary());
    }
}
