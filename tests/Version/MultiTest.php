<?php

namespace Darsyn\IP\Tests\Version;

use Darsyn\IP\Exception\InvalidIpAddressException;
use Darsyn\IP\Exception\WrongVersionException;
use Darsyn\IP\IpInterface;
use Darsyn\IP\Strategy;
use Darsyn\IP\Tests\DataProvider\Multi as MultiDataProvider;
use Darsyn\IP\Version\IPv4;
use Darsyn\IP\Version\IPv6;
use Darsyn\IP\Version\Multi as IP;
use Darsyn\IP\Version\MultiVersionInterface;
use Darsyn\IP\Version\Version4Interface;
use Darsyn\IP\Version\Version6Interface;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class MultiTest extends TestCase
{
    /** @before */
    #[PHPUnit\Before]
    public function resetDefaultEmbeddingStrategy()
    {
        IP::setDefaultEmbeddingStrategy(new Strategy\Mapped);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidIpAddresses')]
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
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getEmbeddingStrategyIpAddresses')]
    public function testEmbeddingStrategy($strategyClass, $expandedAddress, $v4address)
    {
        $ip = IP::factory($v4address, new $strategyClass);
        $this->assertSame($expandedAddress, $ip->getExpandedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getEmbeddingStrategyIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getEmbeddingStrategyIpAddresses')]
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
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidBinarySequences')]
    public function testBinarySequenceIsTheSameOnceInstantiated($value)
    {
        $ip = IP::factory($value);
        $this->assertSame($value, $ip->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidProtocolIpAddresses')]
    public function testProtocolNotationConvertsToCorrectBinarySequence($value, $hex)
    {
        $ip = IP::factory($value);
        $this->assertSame($hex, unpack('H*hex', $ip->getBinary())['hex']);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getInvalidIpAddresses')]
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
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidIpAddresses')]
    public function testGetBinaryAlwaysReturnsA16ByteString($value)
    {
        $ip = IP::factory($value);
        $this->assertSame(16, strlen(bin2hex($ip->getBinary())) / 2);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidIpAddresses')]
    public function testGetCompactedAddressReturnsCorrectString($value, $hex, $expanded, $compacted)
    {
        $ip = IP::factory($value);
        $this->assertSame($compacted, $ip->getCompactedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidProtocolIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidProtocolIpAddresses')]
    public function testGetExpandedAddressReturnsCorrectString($value, $hex, $expanded)
    {
        $ip = IP::factory($value);
        $this->assertSame($expanded, $ip->getExpandedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidIpVersion4Addresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidIpVersion4Addresses')]
    public function testDotAddressReturnsCorrectString($value, $hex, $expanded, $compacted, $dot)
    {
        $ip = IP::factory($value);
        $this->assertSame($dot, $ip->getDotAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidIpVersion6Addresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidIpVersion6Addresses')]
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
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getIpAddressVersions')]
    public function testVersion($value, $version)
    {
        $ip = IP::factory($value);
        $this->assertSame($version, $ip->getVersion());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getNetworkIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getNetworkIpAddresses')]
    public function testNetworkIp($initial, $expected, $cidr)
    {
        $ip = IP::factory($initial);
        $this->assertSame($expected, $ip->getNetworkIp($cidr)->getProtocolAppropriateAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getBroadcastIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getBroadcastIpAddresses')]
    public function testBroadcastIp($initial, $expected, $cidr)
    {
        $ip = IP::factory($initial);
        $this->assertSame($expected, $ip->getBroadcastIp($cidr)->getProtocolAppropriateAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidInRangeIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidInRangeIpAddresses')]
    public function testInRange($first, $second, $cidr)
    {
        $first = IP::factory($first);
        $second = IP::factory($second);
        $this->assertTrue($first->inRange($second, $cidr));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testDifferentVersionsAreInRange()
    {
        $first = IP::factory('127.0.0.1', new Strategy\Mapped);
        $second = IPv6::factory('::1234:5678:abcd:90ef');
        $this->assertTrue($first->inRange($second, 0));
    }

    /** @test */
    #[PHPUnit\Test]
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
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getCommonCidrValues')]
    public function testCommonCidr($first, $second, $expectedCidr)
    {
        $first = IP::factory($first);
        $second = IP::factory($second);
        $this->assertSame($expectedCidr, $first->getCommonCidr($second));
    }

    /** @test */
    #[PHPUnit\Test]
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
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getLinkLocalIpAddresses')]
    public function testIsLinkLocal($value, $isLinkLocal)
    {
        $ip = IP::factory($value);
        $this->assertSame($isLinkLocal, $ip->isLinkLocal());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getMappedLoopbackIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getMappedLoopbackIpAddresses')]
    public function testIsLoopbackMapped($value, $isLoopback)
    {
        $ip = IP::factory($value, new Strategy\Mapped);
        $this->assertSame($isLoopback, $ip->isLoopback());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getCompatibleLoopbackIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getCompatibleLoopbackIpAddresses')]
    public function testIsLoopbackCompatible($value, $isLoopback)
    {
        $ip = IP::factory($value, new Strategy\Compatible);
        if ($ip->getExpandedAddress() === '0000:0000:0000:0000:0000:0000:0000:0001') {
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
    public function testIsLoopbackDerived($value, $isLoopback)
    {
        $ip = IP::factory($value, new Strategy\Derived);
        $this->assertSame($isLoopback, $ip->isLoopback());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getMulticastIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getMulticastIpAddresses')]
    public function testIsMulticast($value, $isMulticast)
    {
        $ip = IP::factory($value);
        $this->assertSame($isMulticast, $ip->isMulticast());

    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getPrivateUseIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getPrivateUseIpAddresses')]
    public function testIsPrivateUse($value, $isPrivateUse)
    {
        $ip = IP::factory($value);
        $this->assertSame($isPrivateUse, $ip->isPrivateUse());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getUnspecifiedIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getUnspecifiedIpAddresses')]
    public function testIsUnspecified($value, $isUnspecified)
    {
        $ip = IP::factory($value);
        $this->assertSame($isUnspecified, $ip->isUnspecified());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getBenchmarkingIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getBenchmarkingIpAddresses')]
    public function testIsBenchmarking($value, $isBenchmarking)
    {
        $ip = IP::factory($value);
        $this->assertSame($isBenchmarking, $ip->isBenchmarking());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getDocumentationIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getDocumentationIpAddresses')]
    public function testIsDocumentation($value, $isDocumentation)
    {
        $ip = IP::factory($value);
        $this->assertSame($isDocumentation, $ip->isDocumentation());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getPublicUseIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getPublicUseIpAddresses')]
    public function testIsPublicUse($value, $isPublicUse)
    {
        $ip = IP::factory($value, new Strategy\Mapped);
        $this->assertSame($isPublicUse, $ip->isPublicUse());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getUniqueLocalIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getUniqueLocalIpAddresses')]
    public function testIsUniqueLocal($value, $isUniqueLocal, $willThrowException)
    {
        $ip = IP::factory($value, new Strategy\Mapped);
        $willThrowException && $this->expectException(WrongVersionException::class);
        $this->assertSame($isUniqueLocal, $ip->isUniqueLocal());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getUnicastIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getUnicastIpAddresses')]
    public function testIsUnicast($value, $isUnicast, $willThrowException)
    {
        $ip = IP::factory($value, new Strategy\Mapped);
        $willThrowException && $this->expectException(WrongVersionException::class);
        $this->assertSame($isUnicast, $ip->isUnicast());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getUnicastGlobalIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getUnicastGlobalIpAddresses')]
    public function testIsUnicastGlobal($value, $isUnicastGlobal, $willThrowException)
    {
        $ip = IP::factory($value, new Strategy\Mapped);
        $willThrowException && $this->expectException(WrongVersionException::class);
        $this->assertSame($isUnicastGlobal, $ip->isUnicastGlobal());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getIsBroadcastIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getIsBroadcastIpAddresses')]
    public function testIsBroadcast($value, $isBroadcast, $willThrowException)
    {
        $ip = IP::factory($value);
        $willThrowException && $this->expectException(WrongVersionException::class);
        $this->assertSame($isBroadcast, $ip->isBroadcast());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getSharedIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getSharedIpAddresses')]
    public function testIsShared($value, $isShared, $willThrowException)
    {
        $ip = IP::factory($value);
        $willThrowException && $this->expectException(WrongVersionException::class);
        $this->assertSame($isShared, $ip->isShared());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getFutureReservedIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getFutureReservedIpAddresses')]
    public function testIsFutureReserved($value, $isFutureReserved, $willThrowException)
    {
        $ip = IP::factory($value);
        $willThrowException && $this->expectException(WrongVersionException::class);
        $this->assertSame($isFutureReserved, $ip->isFutureReserved());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Multi::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(MultiDataProvider::class, 'getValidIpAddresses')]
    public function testStringCasting($value, $hex, $expanded, $compacted, $dot)
    {
        $ip = IP::factory($value);
        $dot !== null
            ? $this->assertSame($dot, (string) $ip)
            : $this->assertSame($compacted, (string) $ip);
    }
}
