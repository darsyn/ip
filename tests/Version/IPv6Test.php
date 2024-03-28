<?php

namespace Darsyn\IP\Tests\Version;

use Darsyn\IP\Exception\InvalidCidrException;
use Darsyn\IP\Exception\InvalidIpAddressException;
use Darsyn\IP\Exception\WrongVersionException;
use Darsyn\IP\IpInterface;
use Darsyn\IP\Strategy\Mapped;
use Darsyn\IP\Tests\DataProvider\IPv4 as IPv4DataProvider;
use Darsyn\IP\Tests\DataProvider\IPv6 as IPv6DataProvider;
use Darsyn\IP\Version\IPv4;
use Darsyn\IP\Version\IPv6 as IP;
use Darsyn\IP\Version\Multi;
use Darsyn\IP\Version\Version6Interface;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class IPv6Test extends TestCase
{
    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidIpAddresses')]
    public function testInstantiationWithValidAddresses($value)
    {
        $ip = IP::factory($value);
        $this->assertInstanceOf(IpInterface::class, $ip);
        $this->assertInstanceOf(Version6Interface::class, $ip);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidBinarySequences()
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidBinarySequences')]
    public function testBinarySequenceIsTheSameOnceInstantiated($value)
    {
        $ip = IP::factory($value);
        $this->assertSame($value, $ip->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     * @param string $value
     * @param string $hex
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testProtocolNotationConvertsToCorrectBinarySequence($value, $hex)
    {
        $ip = IP::factory($value);
        $actualHex = unpack('H*hex', $ip->getBinary());
        $this->assertSame($hex, is_array($actualHex) ? $actualHex['hex'] : null);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getInvalidIpAddresses()
     * @param mixed $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getInvalidIpAddresses')]
    public function testExceptionIsThrownOnInstantiationWithInvalidAddresses($value)
    {
        $this->expectException(\Darsyn\IP\Exception\InvalidIpAddressException::class);
        $this->expectExceptionMessage('The IP address supplied is not valid.');
        try {
            /** @phpstan-ignore argument.type */
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
     * @return void
     */
    #[PHPUnit\Test]
    public function testInstantiationFromEmbeddedIpAddress()
    {
        try {
            $ip = IP::factory('12.34.56.78');
            $this->fail('IPv6 factory should not accept IPv4 addresses.');
        } catch (InvalidIpAddressException $e) {
        }

        // IPv4 address can be embedded into IPv6 objects using the fromEmbedded() static instantiator.
        $embedded = IP::fromEmbedded('12.34.56.78', new Mapped);
        // But IPv6 objects should ignore the fact that it's embedded and only work with the full IPv6 address.
        $this->assertSame('0000:1fff:ffff:ffff:ffff:ffff:ffff:ffff', $embedded->getBroadcastIp(19)->getExpandedAddress());

        // Multi objects understand both IPv4 and IPv6 addresses.
        $multi = Multi::factory('12.34.56.78', new Mapped);
        // So therefore, if a Multi object detects that it holds an embedded IPv4 address it will attempt to work with
        // the IPv4 address before falling back on the full IPv6 address.
        $this->assertSame('0000:0000:0000:0000:0000:ffff:0c22:3fff', $multi->getBroadcastIp(19)->getExpandedAddress());
        $this->assertSame('12.34.63.255', $multi->getBroadcastIp(19)->getDotAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidIpAddresses')]
    public function testGetBinaryAlwaysReturnsA16ByteString($value)
    {
        $ip = IP::factory($value);
        $this->assertSame(16, strlen(bin2hex($ip->getBinary())) / 2);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     * @param string $value
     * @param string $hex
     * @param string $expanded
     * @param string $compacted
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidIpAddresses')]
    public function testGetCompactedAddressReturnsCorrectString($value, $hex, $expanded, $compacted)
    {
        $ip = IP::factory($value);
        $this->assertSame($compacted, $ip->getCompactedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     * @param string $value
     * @param string $hex
     * @param string $expanded
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidProtocolIpAddresses')]
    public function testGetExpandedAddressReturnsCorrectString($value, $hex, $expanded)
    {
        $ip = IP::factory($value);
        $this->assertSame($expanded, $ip->getExpandedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidIpAddresses')]
    public function testGetVersionAlwaysReturns6($value)
    {
        $ip = IP::factory($value);
        $this->assertSame(6, $ip->getVersion());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidIpAddresses')]
    public function testIsVersionOnlyReturnsTrueFor6($value)
    {
        $ip = IP::factory($value);
        $this->assertTrue($ip->isVersion(6));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidIpAddresses')]
    public function testIsVersionOnlyReturnsFalseFor4($value)
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isVersion(4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidIpAddresses')]
    public function testIsVersion6AlwaysReturnsTrue($value)
    {
        $ip = IP::factory($value);
        $this->assertTrue($ip->isVersion6());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidIpAddresses')]
    public function testIsVersion4AlwaysReturnsFalse($value)
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isVersion4());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidCidrValues()
     * @param int $cidr
     * @param string $expectedMaskHex
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidCidrValues')]
    public function testCidrMasks($cidr, $expectedMaskHex)
    {
        $ip = IP::factory('::1');
        $reflect = new \ReflectionClass($ip);
        $method = $reflect->getMethod('generateBinaryMask');
        $method->setAccessible(true);
        /** @phpstan-ignore argument.type */
        $actualMask = unpack('H*hex', $method->invoke($ip, $cidr, 16));
        $this->assertSame($expectedMaskHex, is_array($actualMask) ? $actualMask['hex'] : null);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getInvalidCidrValues()
     * @param mixed $cidr
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getInvalidCidrValues')]
    public function testExceptionIsThrownFromInvalidCidrValues($cidr)
    {
        $this->expectException(\Darsyn\IP\Exception\InvalidCidrException::class);
        $this->expectExceptionMessage('The supplied CIDR is not valid; it must be an integer (between 0 and 128).');
        $ip = IP::factory('::1');
        $reflect = new \ReflectionClass($ip);
        $method = $reflect->getMethod('generateBinaryMask');
        $method->setAccessible(true);
        try {
            $method->invoke($ip, $cidr, 16);
        /** @phpstan-ignore catch.neverThrown */
        } catch (InvalidCidrException $e) {
            $this->assertSame($cidr, $e->getSuppliedCidr());
            throw $e;
        }
        $this->fail();
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getNetworkIpAddresses()
     * @param string $expected
     * @param int $cidr
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getNetworkIpAddresses')]
    public function testNetworkIp($expected, $cidr)
    {
        $ip = IP::factory('2001:db8::a60:8a2e:370:7334');
        $this->assertSame($expected, $ip->getNetworkIp($cidr)->getCompactedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getBroadcastIpAddresses()
     * @param string $expected
     * @param int $cidr
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getBroadcastIpAddresses')]
    public function testBroadcastIp($expected, $cidr)
    {
        $ip = IP::factory('2001:db8::a60:8a2e:370:7334');
        $this->assertSame($expected, $ip->getBroadcastIp($cidr)->getCompactedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidInRangeIpAddresses()
     * @param string $first
     * @param string $second
     * @param int $cidr
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidInRangeIpAddresses')]
    public function testInRange($first, $second, $cidr)
    {
        $first = IP::factory($first);
        $second = IP::factory($second);
        $this->assertTrue($first->inRange($second, $cidr));
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testDifferentVersionsAreNotInRange()
    {
        $ip = IP::factory('::12.34.56.78');
        $other = IPv4::factory('12.34.56.78');
        $this->expectException(WrongVersionException::class);
        $ip->inRange($other, 0);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getCommonCidrValues()
     * @param string $first
     * @param string $second
     * @param int $expectedCidr
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getCommonCidrValues')]
    public function testCommonCidr($first, $second, $expectedCidr)
    {
        $first = IP::factory($first);
        $second = IP::factory($second);
        $this->assertSame($expectedCidr, $first->getCommonCidr($second));
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
    public function testEmbeddedCommonCidr($first, $second, $expectedCidr)
    {
        $first = IP::fromEmbedded($first);
        $second = IP::fromEmbedded($second);
        $this->assertSame(96 + $expectedCidr, $first->getCommonCidr($second));
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testCommonCidrThrowsException()
    {
        $first = IP::factory('2001:db8::a60:8a2e:370:7334');
        $second = IPv4::factory('12.34.56.78');
        $this->expectException(WrongVersionException::class);
        $first->getCommonCidr($second);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getMappedIpAddresses()
     * @param string $value
     * @param bool $isMapped
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getMappedIpAddresses')]
    public function testIsMapped($value, $isMapped)
    {
        $ip = IP::factory($value);
        $this->assertSame($isMapped, $ip->isMapped());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getDerivedIpAddresses()
     * @param string $value
     * @param bool $isDerived
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getDerivedIpAddresses')]
    public function testIsDerived($value, $isDerived)
    {
        $ip = IP::factory($value);
        $this->assertSame($isDerived, $ip->isDerived());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getCompatibleIpAddresses()
     * @param string $value
     * @param bool $isCompatible
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getCompatibleIpAddresses')]
    public function testIsCompatible($value, $isCompatible)
    {
        $ip = IP::factory($value);
        $this->assertSame($isCompatible, $ip->isCompatible());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     * @param string $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidIpAddresses')]
    public function testIsEmbeddedAlwaysReturnsFalse($value)
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isEmbedded());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getLinkLocalIpAddresses()
     * @param string $value
     * @param bool $isLinkLocal
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getLinkLocalIpAddresses')]
    public function testIsLinkLocal($value, $isLinkLocal)
    {
        $ip = IP::factory($value);
        $this->assertSame($isLinkLocal, $ip->isLinkLocal());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getLoopbackIpAddresses()
     * @param string $value
     * @param bool $isLoopback
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getLoopbackIpAddresses')]
    public function testIsLoopback($value, $isLoopback)
    {
        $ip = IP::factory($value);
        $this->assertSame($isLoopback, $ip->isLoopback());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getMulticastIpAddresses()
     * @param string $value
     * @param bool $isMulticast
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getMulticastIpAddresses')]
    public function testIsMulticast($value, $isMulticast)
    {
        $ip = IP::factory($value);
        $this->assertSame($isMulticast, $ip->isMulticast());

    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getPrivateUseIpAddresses()
     * @param string $value
     * @param bool $isPrivateUse
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getPrivateUseIpAddresses')]
    public function testIsPrivateUse($value, $isPrivateUse)
    {
        $ip = IP::factory($value);
        $this->assertSame($isPrivateUse, $ip->isPrivateUse());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getUnspecifiedIpAddresses()
     * @param string $value
     * @param bool $isUnspecified
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getUnspecifiedIpAddresses')]
    public function testIsUnspecified($value, $isUnspecified)
    {
        $ip = IP::factory($value);
        $this->assertSame($isUnspecified, $ip->isUnspecified());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getBenchmarkingIpAddresses()
     * @param string $value
     * @param bool $isBenchmarking
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getBenchmarkingIpAddresses')]
    public function testIsBenchmarking($value, $isBenchmarking)
    {
        $ip = IP::factory($value);
        $this->assertSame($isBenchmarking, $ip->isBenchmarking());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getDocumentationIpAddresses()
     * @param string $value
     * @param bool $isDocumentation
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getDocumentationIpAddresses')]
    public function testIsDocumentation($value, $isDocumentation)
    {
        $ip = IP::factory($value);
        $this->assertSame($isDocumentation, $ip->isDocumentation());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getPublicUseIpAddresses()
     * @param string $value
     * @param bool $isPublicUse
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getPublicUseIpAddresses')]
    public function testIsPublicUse($value, $isPublicUse)
    {
        $ip = IP::factory($value);
        $this->assertSame($isPublicUse, $ip->isPublicUse());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getUniqueLocalIpAddresses()
     * @param string $value
     * @param bool $isUniqueLocal
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getUniqueLocalIpAddresses')]
    public function testIsUniqueLocal($value, $isUniqueLocal)
    {
        $ip = IP::factory($value);
        $this->assertSame($isUniqueLocal, $ip->isUniqueLocal());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getUnicastIpAddresses()
     * @param string $value
     * @param bool $isUnicast
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getUnicastIpAddresses')]
    public function testIsUnicast($value, $isUnicast)
    {
        $ip = IP::factory($value);
        $this->assertSame($isUnicast, $ip->isUnicast());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getUnicastGlobalIpAddresses()
     * @param string $value
     * @param bool $isUnicastGlobal
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getUnicastGlobalIpAddresses')]
    public function testIsUnicastGlobal($value, $isUnicastGlobal)
    {
        $ip = IP::factory($value);
        $this->assertSame($isUnicastGlobal, $ip->isUnicastGlobal());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     * @param string $value
     * @param string $hex
     * @param string $expanded
     * @param string $compacted
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(IPv6DataProvider::class, 'getValidIpAddresses')]
    public function testStringCasting($value, $hex, $expanded, $compacted)
    {
        $ip = IP::factory($value);
        $this->assertSame($compacted, (string) $ip);
    }
}
