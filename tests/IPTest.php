<?php

namespace Darsyn\IP\Tests;

use Darsyn\IP\InvalidIpAddressException;
use Darsyn\IP\IP;
use PHPUnit_Framework_TestCase as TestCase;

class IPTest extends TestCase
{
    protected function setUp()
    {
        if (PHP_INT_SIZE == 4) {
            $this->markTestSkipped('Skipping test that can run only on a 64-bit build of PHP.');
        }
    }

    /**
     * @test
     */
    public function testProtocolToNumber()
    {
        $ipv4 = new IP('12.34.56.78');
        $this->assertSame(16, strlen($ipv4->getBinary()));
        $this->assertSame(pack('H*', '0000000000000000000000000c22384e'), $ipv4->getBinary());

        $ipv6 = new IP('2001:db8::a60:8a2e:370:7334');
        $this->assertSame(16, strlen($ipv6->getBinary()));
        $this->assertSame(pack('H*', '20010db8000000000a608a2e03707334'), $ipv6->getBinary());

        // Test input that has already been converted to binary notation.
        $ip = new IP($ipv6->getBinary());
        $this->assertSame(16, strlen($ip->getBinary()));
        $this->assertSame(pack('H*', '20010db8000000000a608a2e03707334'), $ip->getBinary());
    }

    public function dataProviderValidIpAddresses()
    {
        return [
            ['12.34.56.78', 4],
            ['::12.34.56.78', 4],
            ['::1', 4],
            ['2001:db8::a60:8a2e:370:7334', 6],
            ['2001:0db8:0000:0000:0a60:8a2e:0370:7334', 6],
            ['1234567890123456', 6],
        ];
    }

    public function dataProviderInvalidIpAddresses()
    {
        return [
            ['12.34.567.89'],
            ['2001:db8::a60:8a2e:370g:7334'],
            ['1.2.3'],
            ['This one is completely wrong.'],
            // 15 bytes instead of 16.
            [pack('H*', '20010db8000000000a608a2e037073')],
            [123],
            [1.3],
            [array()],
            [(object) array()],
            [null],
            [true],
            ['123456789012345'],
            ['12345678901234567'],
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderValidIpAddresses
     */
    public function testValidationThroughInstantiation($ipAddress, $version)
    {
        $ip = new IP($ipAddress);
        $this->assertTrue($ip->isVersion($version));
    }

    /**
     * @test
     * @expectedException \Darsyn\IP\InvalidIpAddressException
     * @dataProvider dataProviderInvalidIpAddresses
     */
    public function testExceptionThrownWhenInvalidIpSuppliedDuringInstantiation($ipAddress)
    {
        try {
            new IP($ipAddress);
        } catch (InvalidIpAddressException $e) {
            $this->assertSame($ipAddress, $e->getIp());
            throw $e;
        }
        $this->fail();
    }

    /**
     * @test
     */
    public function testNumberToProtocol()
    {
        $ipv4 = new IP('12.34.56.78');
        $this->assertSame('12.34.56.78', $ipv4->getShortAddress());
        $this->assertSame('0000:0000:0000:0000:0000:0000:0c22:384e', $ipv4->getLongAddress());

        $ipv6 = new IP('2001:db8::a60:8a2e:370:7334');
        $this->assertSame('2001:db8::a60:8a2e:370:7334', $ipv6->getShortAddress());
        $this->assertSame('2001:0db8:0000:0000:0a60:8a2e:0370:7334', $ipv6->getLongAddress());
    }

    public function dataProviderValidCidrMasks()
    {
        return [
            ['00000000000000000000000000000000', 0],
            ['ffffffffffffffffffffffffffffffff', 128],
            ['ffffffffffffffff0000000000000000', 64],
            ['80000000000000000000000000000000', 1],
            ['c0000000000000000000000000000000', 2],
            ['e0000000000000000000000000000000', 3],
            ['f0000000000000000000000000000000', 4],
            ['f8000000000000000000000000000000', 5],
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderValidCidrMasks
     */
    public function testCidrMasks($expectedMask, $cidr)
    {
        $class = new \ReflectionClass(IP::class);
        $method = $class->getMethod('getMask');
        $method->setAccessible(true);
        $ip = new IP('12.34.56.78');
        $this->assertSame(pack('H*', $expectedMask), $method->invoke($ip, $cidr));
    }

    public function dataProviderInvalidCidrValues()
    {
        return array(
            array(-1),
            array(129),
            array('0'),
            array('128'),
            array(12.3),
            array(true),
            array(null),
            array(array()),
            array((object) array()),
        );
    }

    /**
     * @test
     * @dataProvider dataProviderInvalidCidrValues
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidCidrValues($cidr)
    {
        $class = new \ReflectionClass(IP::class);
        $method = $class->getMethod('getMask');
        $method->setAccessible(true);
        $ip = new IP('12.34.56.78');
        $method->invoke($ip, $cidr);
    }

    public function dataProviderValidNetworkAddressesVersion4()
    {
        return [
            ['12.34.56.78', 32],
            ['12.34.56.0', 24],
            ['12.34.48.0', 20],
            ['12.34.0.0', 16],
            ['12.32.0.0', 13],
            ['12.0.0.0', 8],
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderValidNetworkAddressesVersion4
     */
    public function testNetworkAddressesVersion4($expected, $cidr)
    {
        $ip = new IP('12.34.56.78');
        // Because we are working with IPv4 addresses, and the network CIDR is
        // for IPv6 we need to convert it by adding 96.
        $this->assertSame($expected, $ip->getNetworkIp(IP::CIDR4TO6 + $cidr)->getShortAddress());
    }

    public function dataProviderValidNetworkAddressesVersion6()
    {
        return [
            ['2000::', 12],
            ['2001:db8::', 59],
            ['2001:db8:0:0:800::', 70],
            ['2001:db8::a60:8a2e:0:0', 99],
            ['2001:db8::a60:8a2e:370:7334', 128],
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderValidNetworkAddressesVersion6
     */
    public function testNetworkAddressesVersion6($expected, $cidr)
    {
        $ip = new IP('2001:db8::a60:8a2e:370:7334');
        $this->assertSame($expected, $ip->getNetworkIp($cidr)->getShortAddress());
    }

    public function dataProviderValidBroadcastAddressesVersion4()
    {
        return [
            ['12.34.56.78', 32],
            ['12.34.56.255', 24],
            ['12.34.63.255', 20],
            ['12.34.255.255', 16],
            ['12.39.255.255', 13],
            ['12.255.255.255', 8],
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderValidBroadcastAddressesVersion4
     */
    public function testBroadcastAddressesVersion4($expected, $cidr)
    {
        $ip = new IP('12.34.56.78');
        // Because we are working with IPv4 addresses, and the network CIDR is
        // for IPv6 we need to convert it by adding 96.
        $this->assertSame($expected, $ip->getBroadcastIp(IP::CIDR4TO6 + $cidr)->getShortAddress());
    }

    public function dataProviderValidBroadcastAddressesVersion6()
    {
        return [
            ['200f:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 12],
            ['2001:db8:0:1f:ffff:ffff:ffff:ffff', 59],
            ['2001:db8::bff:ffff:ffff:ffff', 70],
            ['2001:db8::a60:8a2e:1fff:ffff', 99],
            ['2001:db8::a60:8a2e:370:7334', 128],
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderValidBroadcastAddressesVersion6
     */
    public function testBroadcastAddressesVersion6($expected, $cidr)
    {
        $ip = new IP('2001:db8::a60:8a2e:370:7334');
        $this->assertSame($expected, $ip->getBroadcastIp($cidr)->getShortAddress());
    }

    public function dataProviderInRangeAddressesVersion4()
    {
        return [
            ['12.34.56.78', '12.34.56.78', 32],
            ['0.0.0.1', '255.255.255.254', 0],
            ['12.34.143.96', '12.34.201.26', 16],
            ['12.34.255.252', '12.34.255.255', 30],
            ['::cff:103', '12.255.255.255', 5],
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderInRangeAddressesVersion4
     */
    public function testInRangeAddressesVersion4($ip1, $ip2, $cidr)
    {
        $ip1 = new IP($ip1);
        $ip2 = new IP($ip2);
        $this->assertTrue($ip1->inRange($ip2, IP::CIDR4TO6 + $cidr));
    }

    public function dataProviderNotInRangeAddressesVersion4()
    {
        return [
            ['0.0.0.1', '255.255.255.254', 1],
            ['12.34.143.96', '12.34.201.26', 18],
            ['12.34.255.230', '12.34.255.255', 31],
            ['::a8f2:103', '12.255.255.255', 5],
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderNotInRangeAddressesVersion4
     */
    public function testNotInRangeAddressesVersion4($ip1, $ip2, $cidr)
    {
        $ip1 = new IP($ip1);
        $ip2 = new IP($ip2);
        $this->assertFalse($ip1->inRange($ip2, 96 + $cidr));
    }

    /**
     * @test
     */
    public function testIpObjectToString()
    {
        $ip = new IP('12.34.56.78');
        $expected = pack('H*', '0000000000000000000000000c22384e');
        $this->assertSame($expected, (string) $ip);
        $this->assertSame($expected, $ip->__toString());
    }

    public function dataProviderIpAddressesVersion4()
    {
        return [
            ['12.34.56.78',     IP::VERSION_4],
            ['192.168.33.10',   IP::VERSION_4],
            ['255.255.255.255', IP::VERSION_4],
            ['8.8.8.8',         IP::VERSION_4],
            // Double check that this is reported as version 4 rather than the version 6
            // it looks like (due to the way versions are determined internally).
            ['::1',             IP::VERSION_4],
            // And finally, just check that it can properly detect a version 4
            // address in version 4/6 notation.
            ['::0:12.34.56.78', IP::VERSION_4],
            ['::ffff:7f00:1',   IP::VERSION_4],
        ];
    }

    public function dataProviderIpAddressesVersion6()
    {
        return [
            ['2001:4860:4860::8844', IP::VERSION_6],
            ['fd0a:238b:4a96::',     IP::VERSION_6],
        ];
    }

    public function dataProviderIpAddressesMixedVersions()
    {
        return array_merge($this->dataProviderIpAddressesVersion4(), $this->dataProviderIpAddressesVersion6());
    }

    /**
     * @test
     * @dataProvider dataProviderIpAddressesMixedVersions
     */
    public function testDetectedVersionIsCorrect($ip, $version)
    {
        $ip = new IP($ip);
        $notVersion = $version === 4 ? 6 : 4;
        $this->assertSame($version, $ip->getVersion());
        $this->assertTrue($ip->isVersion($version));
        $this->assertFalse($ip->isVersion($notVersion));
    }

    /**
     * @test
     * @dataProvider dataProviderIpAddressesVersion4
     */
    public function testVersion4Detection($ip, $version)
    {
        $ip = new IP($ip);
        $this->assertSame($version, $ip->getVersion());
        $this->assertTrue($ip->isVersion4());
    }

    /**
     * @test
     * @dataProvider dataProviderIpAddressesVersion6
     */
    public function testVersion6Detection($ip, $version)
    {
        $ip = new IP($ip);
        $this->assertSame($version, $ip->getVersion());
        $this->assertTrue($ip->isVersion6());
    }

    public function dataProviderMappedIpAddresses()
    {
        return [
            ['::ffff:7f00:1', true],
            ['::ffff:1234:5678', true],
            ['0000:0000:0000:0000:0000:ffff:7f00:a001', true],
            ['::fff:7f00:1', false],
            ['a::ffff:7f00:1', false],
            ['2001:db8::a60:8a2e:370:7334', false],
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderMappedIpAddresses
     */
    public function testIsMapped($ip, $isMapped)
    {
        $ip = new IP($ip);
        $this->assertSame($isMapped, $ip->isMapped());
    }

    public function dataProviderDerivedIppAddresses()
    {
        return [
            ['2002::', true],
            ['2002:7f00:1::', true],
            ['2002:1234:4321:0:00:000:0000::', true],
            ['2001:7f00:1::', false],
            ['2002:7f00:1::a', false],
            ['127.0.0.1', false],
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderDerivedIppAddresses
     */
    public function testIsDerived($ip, $isDerived)
    {
        $ip = new IP($ip);
        $this->assertSame($isDerived, $ip->isDerived());
    }

    public function dataProviderCompatibleIpAddresses()
    {
        return [
            ['::7f00:1', true],
            ['127.0.0.1', true],
            ['2002:7f00:1::', false],
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderCompatibleIpAddresses
     */
    public function testIsCompatible($ip, $isCompatible)
    {
        $ip = new IP($ip);
        $this->assertSame($isCompatible, $ip->isCompatible());
    }

    public function dataProviderEmbeddedIpAddresses()
    {
        return [
            ['::ffff:7f00:1', true],
            ['::ffff:1234:5678', true],
            ['0000:0000:0000:0000:0000:ffff:7f00:a001', true],
            ['::fff:7f00:1', false],
            ['a::ffff:7f00:1', false],
            ['2001:db8::a60:8a2e:370:7334', false],
            ['::7f00:1', true],
            ['127.0.0.1', true],
            ['2002:7f00:1::', false],
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderEmbeddedIpAddresses
     */
    public function testIsEmbedded($ip, $isEmbedded)
    {
        $ip = new IP($ip);
        $this->assertSame($isEmbedded, $ip->isEmbedded());
    }

    public function dataProviderLinkLocalIpAddresses()
    {
        return [
            ['169.253.255.255', false],
            ['169.254.0.0', true],
            ['169.254.255.255', true],
            ['169.255.0.0', false],
            ['fe7f:ffff:ffff:ffff:ffff:ffff:ffff:ffff', false],
            ['fe80::', true],
            ['febf:ffff:ffff:ffff:ffff:ffff:ffff:ffff', true],
            ['fec0::', false],
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderLinkLocalIpAddresses
     */
    public function testIsLinkLocal($ip, $isLinkLocal)
    {
        $ip = new IP($ip);
        $this->assertEquals($isLinkLocal, $ip->isLinkLocal());
    }

    public function dataProviderLoopbackIpAddresses()
    {
        return [
            ['126.255.255.255', false],
            ['127.0.0.0', true],
            ['127.255.255.255', true],
            ['128.0.0.0', false],
            ['::1', true],
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderLoopbackIpAddresses
     */
    public function testIsLoopback($ip, $isLoopback)
    {
        $ip = new IP($ip);
        $this->assertEquals($isLoopback, $ip->isLoopback());
    }

    public function dataProviderMultiCastIpAddresses()
    {
        return [
            ['223.255.255.255', false],
            ['224.0.0.0', true],
            ['239.255.255.255', true],
            ['240.0.0.0', false],
            ['feff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', false],
            ['ff00::', true],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', true],
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderMultiCastIpAddresses
     */
    public function testIsMulticast($ip, $isMulticast)
    {
        $ip = new IP($ip);
        $this->assertEquals($isMulticast, $ip->isMulticast());
    }

    public function dataProviderUnspecifiedIpAddresses()
    {
        return [
            ['0.0.0.0'],
            ['::0'],
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderUnspecifiedIpAddresses
     */
    public function testIsUnspecified($ip)
    {
        $ip = new IP($ip);
        $this->assertTrue($ip->isUnspecified());
    }

    public function dataProviderPrivateUseIpAddresses()
    {
        return [
            ['9.255.255.255', false],
            ['10.0.0.0', true],
            ['10.255.255.255', true],
            ['11.0.0.0', false],
            ['172.15.255.255', false],
            ['172.16.0.0', true],
            ['172.31.255.255', true],
            ['172.32.0.0', false],
            ['192.167.255.255', false],
            ['192.168.0.0', true],
            ['192.168.255.255', true],
            ['192.169.0.0', false],
            ['fcff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', false],
            ['fd00::', true],
            ['fdff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', true],
            ['fe00::', false],
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderPrivateUseIpAddresses
     */
    public function testIsPrivateUse($ip, $isPrivateUse)
    {
        $ip = new IP($ip);
        $this->assertEquals($isPrivateUse, $ip->isPrivateUse());
    }
}
