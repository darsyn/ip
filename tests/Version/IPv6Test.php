<?php declare(strict_types=1);

namespace Darsyn\IP\Tests\Version;

use Darsyn\IP\Binary;
use Darsyn\IP\Exception\InvalidCidrException;
use Darsyn\IP\Exception\InvalidIpAddressException;
use Darsyn\IP\IpInterface;
use Darsyn\IP\Tests\TestCase;
use Darsyn\IP\Version\IPv4;
use Darsyn\IP\Version\IPv6 as IP;
use Darsyn\IP\Version\Version6Interface;

class IPv6Test extends TestCase
{
    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     */
    public function testInstantiationWithValidAddresses($value): void
    {
        $ip = IP::factory($value);
        $this->assertInstanceOf(IpInterface::class, $ip);
        $this->assertInstanceOf(Version6Interface::class, $ip);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidBinarySequences()
     */
    public function testBinarySequenceIsTheSameOnceInstantiated($value): void
    {
        $ip = IP::factory($value);
        $this->assertSame($value, $ip->getBinary());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     */
    public function testProtocolNotationConvertsToCorrectBinarySequence($value, $hex): void
    {
        $ip = IP::factory($value);
        $this->assertSame($hex, unpack('H*hex', $ip->getBinary())['hex']);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getInvalidIpAddresses()
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
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     */
    public function testGetBinaryAlwaysReturnsA16ByteString($value): void
    {
        $ip = IP::factory($value);
        $this->assertSame(16, strlen(bin2hex($ip->getBinary())) / 2);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     */
    public function testGetCompactedAddressReturnsCorrectString($value, $hex, $expanded, $compacted): void
    {
        $ip = IP::factory($value);
        $this->assertSame($compacted, $ip->getCompactedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidProtocolIpAddresses()
     */
    public function testGetExpandedAddressReturnsCorrectString($value, $hex, $expanded): void
    {
        $ip = IP::factory($value);
        $this->assertSame($expanded, $ip->getExpandedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     */
    public function testGetVersionAlwaysReturns6($value): void
    {
        $ip = IP::factory($value);
        $this->assertSame(6, $ip->getVersion());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     */
    public function testIsVersionOnlyReturnsTrueFor6($value): void
    {
        $ip = IP::factory($value);
        $this->assertTrue($ip->isVersion(6));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     */
    public function testIsVersionOnlyReturnsFalseFor4($value): void
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isVersion(4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     */
    public function testIsVersion6AlwaysReturnsTrue($value): void
    {
        $ip = IP::factory($value);
        $this->assertTrue($ip->isVersion6());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     */
    public function testIsVersion4AlwaysReturnsFalse($value): void
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isVersion4());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidCidrValues()
     */
    public function testCidrMasks($cidr, $expectedMaskHex): void
    {
        $ip = IP::factory('::1');
        $reflect = new \ReflectionClass($ip);
        $method = $reflect->getMethod('generateBinaryMask');
        $method->setAccessible(true);
        $this->assertSame($expectedMaskHex, unpack('H*hex', $method->invoke($ip, $cidr, 16))['hex']);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getInvalidCidrValues()
     * @expectedException \Darsyn\IP\Exception\InvalidCidrException
     * @expectedExceptionMessage The CIDR supplied is not valid; it must be an integer between 0 and 128.
     */
    public function testExceptionIsThrownFromInvalidCidrValues($cidr): void
    {
        $ip = IP::factory('::1');
        // Create a wrapper bound to the AbstractIP class to access the
        // protected method. Use this method instead of reflection because
        // invoking a ReflectionMethod does not adhere to strict typing.
        $generateBinaryMask = (function ($cidr): string {
            return $this->generateBinaryMask($cidr, 16);
        })->bindTo($ip, $ip);

        try {
            /** @var string $mask */
            $mask = $generateBinaryMask($cidr);
            $this->fail(sprintf(
                'Mask "%s" generated successfully from %s evaluated to %s, expected failure.',
                Binary::toHex($mask),
                var_export($cidr, true),
                var_export((int) $cidr, true)
            ));
        } catch (InvalidCidrException $e) {
            $this->assertSame($cidr, $e->getSuppliedCidr());
            throw $e;
        } catch (\TypeError $e) {
            $this->assertNotInternalType('int', $cidr);
            // If a TypeError is thrown, it means that CIDR is the wrong data
            // type so therefore automatically invalid. Throw the appropriate
            // exception here so that PHPUnit can pick it up.
            throw new InvalidCidrException(0, 16, $e);
        }
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getNetworkIpAddresses()
     */
    public function testNetworkIp($expected, $cidr): void
    {
        $ip = IP::factory('2001:db8::a60:8a2e:370:7334');
        $this->assertSame($expected, $ip->getNetworkIp($cidr)->getCompactedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getBroadcastIpAddresses()
     */
    public function testBroadcastIp($expected, $cidr): void
    {
        $ip = IP::factory('2001:db8::a60:8a2e:370:7334');
        $this->assertSame($expected, $ip->getBroadcastIp($cidr)->getCompactedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidInRangeIpAddresses()
     */
    public function testInRange($first, $second, $cidr): void
    {
        $first = IP::factory($first);
        $second = IP::factory($second);
        $this->assertTrue($first->inRange($second, $cidr));
    }

    /**
     * @test
     */
    public function testDifferentVersionsAreNotInRange(): void
    {
        $ip = IP::factory('::12.34.56.78');
        $other = IPv4::factory('12.34.56.78');
        $this->assertFalse($ip->inRange($other, 0));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getMappedIpAddresses()
     */
    public function testIsMapped($value, $isMapped): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isMapped, $ip->isMapped());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getDerivedIpAddresses()
     */
    public function testIsDerived($value, $isDerived): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isDerived, $ip->isDerived());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getCompatibleIpAddresses()
     */
    public function testIsCompatible($value, $isCompatible): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isCompatible, $ip->isCompatible());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getValidIpAddresses()
     */
    public function testIsEmbeddedAlwaysReturnsFalse($value): void
    {
        $ip = IP::factory($value);
        $this->assertFalse($ip->isEmbedded());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getLinkLocalIpAddresses()
     */
    public function testIsLinkLocal($value, $isLinkLocal): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isLinkLocal, $ip->isLinkLocal());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getLoopbackIpAddresses()
     */
    public function testIsLoopback($value, $isLoopback): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isLoopback, $ip->isLoopback());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getMulticastIpAddresses()
     */
    public function testIsMulticast($value, $isMulticast): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isMulticast, $ip->isMulticast());

    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getPrivateUseIpAddresses()
     */
    public function testIsPrivateUse($value, $isPrivateUse): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isPrivateUse, $ip->isPrivateUse());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\IPv6::getUnspecifiedIpAddresses()
     */
    public function testIsUnspecified($value, $isUnspecified): void
    {
        $ip = IP::factory($value);
        $this->assertSame($isUnspecified, $ip->isUnspecified());
    }
}
