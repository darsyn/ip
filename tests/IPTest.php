<?php

namespace Darsyn\IP\Tests;

use Darsyn\IP\InvalidIpAddressException;
use Darsyn\IP\IP;

class IPTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Setup
     *
     * @access protected
     * @return void
     */
    protected function setUp()
    {
        if (PHP_INT_SIZE == 4) {
            $this->markTestSkipped('Skipping test that can run only on a 64-bit build of PHP.');
        }
    }

    /**
     * Test: Protocol to Number (Protocol Inputs)
     *
     * @test
     * @access public
     * @return void
     */
    public function pton()
    {
        $ipv4 = new IP('12.34.56.78');
        $this->assertSame(16, strlen($ipv4->getBinary()));
        $this->assertSame(pack('H*', '000000000000000000000ffffc22384e'), $ipv4->getBinary());

        $ipv6 = new IP('2001:db8::a60:8a2e:370:7334');
        $this->assertSame(16, strlen($ipv6->getBinary()));
        $this->assertSame(pack('H*', '20010db8000000000a608a2e03707334'), $ipv6->getBinary());

        // Test input that has already been converted to binary notation.
        $ip = new IP($ipv6->getBinary());
        $this->assertSame(16, strlen($ip->getBinary()));
        $this->assertSame(pack('H*', '20010db8000000000a608a2e03707334'), $ip->getBinary());
    }

    /**
     * Data Provider: Valid IP Addresses
     *
     * @access public
     * @return array
     */
    public function validIpAddresses()
    {
        return array(
            array('12.34.56.78', 4),
            array('::12.34.56.78', 4),
            array('::1', 6),
            array('2001:db8::a60:8a2e:370:7334', 6),
            array('2001:0db8:0000:0000:0a60:8a2e:0370:7334', 6),
            array('1234567890123456', 6),
            // IPv4-mapped IPv6 address
            array('::ffff:1234:5678', 4),
            // 6to4-derived address
            array('2002:1234:5678::', 4),
        );
    }

    /**
     * Data Provider: Invalid IP Addresses
     *
     * @access public
     * @return array
     */
    public function invalidIpAddresses()
    {
        return array(
            array('12.34.567.89'),
            array('2001:db8::a60:8a2e:370g:7334'),
            array('1.2.3'),
            array('This one is completely wrong.'),
            // 15 bytes instead of 16.
            array(pack('H*', '20010db8000000000a608a2e037073')),
            array(123),
            array(1.3),
            array(array()),
            array((object) array()),
            array(null),
            array(true),
            array('123456789012345'),
            array('12345678901234567'),
        );
    }

    /**
     * Test: Validation Through Instantiation
     *
     * @test
     * @dataProvider validIpAddresses
     * @param  string $ipAddress
     * @param  integer $version
     * @return void
     */
    public function validationThroughInstantiation($ipAddress, $version)
    {
        $ip = new IP($ipAddress);
        $this->assertTrue($ip->isVersion($version));
    }

    /**
     * Test: Invalid IP Addresses
     *
     * @test
     * @expectedException \Darsyn\IP\InvalidIpAddressException
     * @dataProvider invalidIpAddresses
     * @param string $ipAddress
     * @return void
     */
    public function ptonInvalid($ipAddress)
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
     * Test: Number to Protocol (Protocol Inputs)
     *
     * @test
     * @access public
     * @return void
     */
    public function ntop()
    {
        $ipv4 = new IP('12.34.56.78');
        $this->assertSame('12.34.56.78', $ipv4->getShortAddress());
        $this->assertSame('0000:0000:0000:0000:0000:0000:0c22:384e', $ipv4->getLongAddress());

        $ipv6 = new IP('2001:db8::a60:8a2e:370:7334');
        $this->assertSame('2001:db8::a60:8a2e:370:7334', $ipv6->getShortAddress());
        $this->assertSame('2001:0db8:0000:0000:0a60:8a2e:0370:7334', $ipv6->getLongAddress());
    }

    /**
     * Test: CIDR Mask
     *
     * @test
     * @access public
     * @return void
     */
    public function masks()
    {
        $class = new \ReflectionClass('Darsyn\IP\IP');
        $method = $class->getMethod('getMask');
        $method->setAccessible(true);

        $ip = new IP('12.34.56.78');

        $this->assertSame(pack('H*', '00000000000000000000000000000000'), $method->invoke($ip, 0));
        $this->assertSame(pack('H*', 'ffffffffffffffffffffffffffffffff'), $method->invoke($ip, 128));
        $this->assertSame(pack('H*', 'ffffffffffffffff0000000000000000'), $method->invoke($ip, 64));
        $this->assertSame(pack('H*', '80000000000000000000000000000000'), $method->invoke($ip, 1));
        $this->assertSame(pack('H*', 'c0000000000000000000000000000000'), $method->invoke($ip, 2));
        $this->assertSame(pack('H*', 'e0000000000000000000000000000000'), $method->invoke($ip, 3));
        $this->assertSame(pack('H*', 'f0000000000000000000000000000000'), $method->invoke($ip, 4));
        $this->assertSame(pack('H*', 'f8000000000000000000000000000000'), $method->invoke($ip, 5));
    }

    /**
     * Data Provider: CIDRs
     *
     * @access public
     * @return array
     */
    public function invalidCIDRs()
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
     * Test: CIDR Mask
     *
     * @test
     * @dataProvider invalidCIDRs
     * @expectedException \InvalidArgumentException
     * @access public
     * @param integer $cidr
     * @return void
     */
    public function invalidMasks($cidr)
    {
        $class = new \ReflectionClass('Darsyn\IP\IP');
        $method = $class->getMethod('getMask');
        $method->setAccessible(true);

        $ip = new IP('12.34.56.78');

        $method->invoke($ip, $cidr);
    }

    /**
     * Data Provider: Valid Network Addresses (V4)
     *
     * @access public
     * @return array
     */
    public function validNetworkAddresses4()
    {
        return array(
            array('12.34.56.78', 32),
            array('12.34.56.0', 24),
            array('12.34.48.0', 20),
            array('12.34.0.0', 16),
            array('12.32.0.0', 13),
            array('12.0.0.0', 8),
        );
    }

    /**
     * Test: Network Addresses
     *
     * @test
     * @dataProvider validNetworkAddresses4
     * @param string $expected
     * @param integer $cidr
     * @return void
     */
    public function networkAddresses($expected, $cidr)
    {
        $ip = new IP('12.34.56.78');
        // Because we are working with IPv4 addresses, and the network CIDR is
        // for IPv6 we need to convert it by adding 96.
        $this->assertSame($expected, $ip->getNetworkIp(96 + $cidr)->getShortAddress());
    }

    /**
     * Data Provider: Valid Network Addresses (V6)
     *
     * @access public
     * @return array
     */
    public function validNetworkAddresses6()
    {
        return array(
            array('2000::', 12),
            array('2001:db8::', 59),
            array('2001:db8:0:0:800::', 70),
            array('2001:db8::a60:8a2e:0:0', 99),
            array('2001:db8::a60:8a2e:370:7334', 128),
        );
    }

    /**
     * Test: (V6) Network Addresses
     *
     * @test
     * @dataProvider validNetworkAddresses6
     * @access public
     * @param string $expected
     * @param int $cidr
     * @return void
     */
    public function v6networkAddresses($expected, $cidr)
    {
        $ip = new IP('2001:db8::a60:8a2e:370:7334');
        $this->assertSame($expected, $ip->getNetworkIp($cidr)->getShortAddress());
    }

    /**
     * Data Provider: Valid Broadcast Addresses (V4)
     *
     * @access public
     * @return array
     */
    public function validBroadcastAddresses4()
    {
        return array(
            array('12.34.56.78', 32),
            array('12.34.56.255', 24),
            array('12.34.63.255', 20),
            array('12.34.255.255', 16),
            array('12.39.255.255', 13),
            array('12.255.255.255', 8),
        );
    }

    /**
     * Test: Broadcast Address
     *
     * @test
     * @dataProvider validBroadcastAddresses4
     * @access public
     * @param string $expected
     * @param integer $cidr
     * @return void
     */
    public function broadcastAddress($expected, $cidr)
    {
        $ip = new IP('12.34.56.78');
        // Because we are working with IPv4 addresses, and the network CIDR is
        // for IPv6 we need to convert it by adding 96.
        $this->assertSame($expected, $ip->getBroadcastIp(96 + $cidr)->getShortAddress());
    }

    /**
     * Data Provider: Valid Broadcast Addresses (V6)
     *
     * @access public
     * @return array
     */
    public function validBroadcastAddresses6()
    {
        return array(
            array('200f:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 12),
            array('2001:db8:0:1f:ffff:ffff:ffff:ffff', 59),
            array('2001:db8::bff:ffff:ffff:ffff', 70),
            array('2001:db8::a60:8a2e:1fff:ffff', 99),
            array('2001:db8::a60:8a2e:370:7334', 128),
        );
    }

    /**
     * Test: (V6) Broadcast Addresses
     *
     * @test
     * @dataProvider validBroadcastAddresses6
     * @access public
     * @param string $expected
     * @param integer $cidr
     * @return void
     */
    public function v6broadcastAddresses($expected, $cidr)
    {
        $ip = new IP('2001:db8::a60:8a2e:370:7334');
        $this->assertSame($expected, $ip->getBroadcastIp($cidr)->getShortAddress());
    }

    /**
     * Data Provider: In-Range IP Addresses
     *
     * @access public
     * @return array
     */
    public function inRangeIPs()
    {
        return array(
            array('12.34.56.78', '12.34.56.78', 32),
            array('0.0.0.1', '255.255.255.254', 0),
            array('12.34.143.96', '12.34.201.26', 16),
            array('12.34.255.252', '12.34.255.255', 30),
            array('::cff:103', '12.255.255.255', 5),
        );
    }

    /**
     * Test: In Range
     *
     * @test
     * @dataProvider inRangeIPs
     * @access public
     * @param string $ip1
     * @param string $ip2
     * @param integer $cidr
     * @return void
     */
    public function inRange($ip1, $ip2, $cidr)
    {
        $ip1 = new IP($ip1);
        $ip2 = new IP($ip2);
        $this->assertTrue($ip1->inRange($ip2, 96 + $cidr));
    }

    /**
     * Data Provider: Not In-Range IP Addresses
     *
     * @access public
     * @return array
     */
    public function notInRangeIPs()
    {
        return array(
            array('0.0.0.1', '255.255.255.254', 1),
            array('12.34.143.96', '12.34.201.26', 18),
            array('12.34.255.230', '12.34.255.255', 31),
            array('::a8f2:103', '12.255.255.255', 5),
        );
    }

    /**
     * Test: Not In Range
     *
     * @test
     * @dataProvider notInRangeIPs
     * @access public
     * @param string $ip1
     * @param string $ip2
     * @param integer $cidr
     * @return void
     */
    public function notInRange($ip1, $ip2, $cidr)
    {
        $ip1 = new IP($ip1);
        $ip2 = new IP($ip2);
        $this->assertFalse($ip1->inRange($ip2, 96 + $cidr));
    }

    /**
     * Test: To String
     *
     * @test
     * @access public
     * @return void
     */
    public function toString()
    {
        $ip = new IP('12.34.56.78');
        $this->assertSame(pack('H*', '0000000000000000000000000c22384e'), (string) $ip);
    }

    /**
     * Data Provider: Example IP Addresses (Version 4)
     *
     * @access public
     * @return array
     */
    public function ipAddressesVersion4()
    {
        return array(
            array('12.34.56.78', IP::VERSION_4),
            array('192.168.33.10', IP::VERSION_4),
            array('255.255.255.255', IP::VERSION_4),
            array('8.8.8.8', IP::VERSION_4),
            // Double check that this is reported as version 4 rather than the version 6
            // it looks like (due to the way versions are determined internally).
            array('::1', IP::VERSION_4),
            // And finally, just check that it can properly detect a version 4
            // address in version 4/6 notation.
            array('::0:12.34.56.78', IP::VERSION_4),
            array('::ffff:7f00:1', IP::VERSION_4),
        );
    }

    /**
     * Data Provider: Example IP Addresses (Version 6)
     *
     * @access public
     * @return array
     */
    public function ipAddressesVersion6()
    {
        return array(
            array('2001:4860:4860::8844', IP::VERSION_6),
            array('fd0a:238b:4a96::', IP::VERSION_6),
        );
    }

    /**
     * Data Provider: Example IP Addresses (Mixed Versions)
     *
     * @access public
     * @return array
     */
    public function ipAddresses()
    {
        return array_merge($this->ipAddressesVersion4(), $this->ipAddressesVersion6());
    }

    /**
     * Test: Get and Is Version
     *
     * @test
     * @dataProvider ipAddresses
     * @access public
     * @param string $ip
     * @param integer $version
     * @return void
     */
    public function getAndIsVersion($ip, $version)
    {
        $ip = new IP($ip);
        $notVersion = $version === 4 ? 6 : 4;
        $this->assertSame($version, $ip->getVersion());
        $this->assertTrue($ip->isVersion($version));
        $this->assertFalse($ip->isVersion($notVersion));
    }

    /**
     * Test: Is Version 4
     *
     * @test
     * @dataProvider ipAddressesVersion4
     * @access public
     * @param string $ip
     * @param integer $version
     * @return void
     */
    public function isVersion4($ip, $version)
    {
        $ip = new IP($ip);
        $this->assertSame($version, $ip->getVersion());
        $this->assertTrue($ip->isVersion4());
    }

    /**
     * Test: Is Version 6
     *
     * @test
     * @dataProvider ipAddressesVersion6
     * @access public
     * @param string $ip
     * @param integer $version
     * @return void
     */
    public function isVersion6($ip, $version)
    {
        $ip = new IP($ip);
        $this->assertSame($version, $ip->getVersion());
        $this->assertTrue($ip->isVersion6());
    }

    public function ipAddressesMapped()
    {
        return array(
            array('::ffff:7f00:1', true),
            array('::ffff:1234:5678', true),
            array('0000:0000:0000:0000:0000:ffff:7f00:a001', true),
            array('::fff:7f00:1', false),
            array('a::ffff:7f00:1', false),
            array('2001:db8::a60:8a2e:370:7334', false),
        );
    }

    /**
     * Test: Is Mapped?
     *
     * @test
     * @dataProvider ipAddressesMapped
     * @param string $ip
     * @param bool $isMapped
     */
    public function isMapped($ip, $isMapped)
    {
        $ip = new IP($ip);
        $this->assertSame($isMapped, $ip->isMapped());
    }

    public function ipAddressesDerived()
    {
        return array(
            array('2002::', true),
            array('2002:7f00:1::', true),
            array('2002:1234:4321:0:00:000:0000::', true),
            array('2001:7f00:1::', false),
            array('2002:7f00:1::a', false),
            array('127.0.0.1', false),
        );
    }

    /**
     * Test: Is Derived
     *
     * @test
     * @dataProvider ipAddressesDerived
     * @access public
     * @param string $ip
     * @param bool $isDerived
     */
    public function isDerived($ip, $isDerived)
    {
        $ip = new IP($ip);
        $this->assertSame($isDerived, $ip->isDerived());
    }

    /**
     * Data Provider: Link Local IP Addresses
     *
     * @access public
     * @return array
     */
    public function linkLocalIpAddresses()
    {
        return array(
            array('169.253.255.255', false),
            array('169.254.0.0', true),
            array('169.254.255.255', true),
            array('169.255.0.0', false),
            array('fe7f:ffff:ffff:ffff:ffff:ffff:ffff:ffff', false),
            array('fe80::', true),
            array('febf:ffff:ffff:ffff:ffff:ffff:ffff:ffff', true),
            array('fec0::', false),
        );
    }

    /**
     * Test: Is link local
     *
     * @test
     * @dataProvider linkLocalIpAddresses
     * @param string $ip
     * @param bool $isLinkLocal
     * @return void
     */
    public function isLinkLocal($ip, $isLinkLocal)
    {
        $ip = new IP($ip);
        $this->assertEquals($isLinkLocal, $ip->isLinkLocal());
    }

    /**
     * Data Provider: Loopback IP Addresses
     *
     * @access public
     * @return array
     */
    public function loopbackIpAddresses()
    {
        return array(
            array('126.255.255.255', false),
            array('127.0.0.0', true),
            array('127.255.255.255', true),
            array('128.0.0.0', false),
            array('::1', true),
        );
    }

    /**
     * Test: Is loopback
     *
     * @test
     * @dataProvider loopbackIpAddresses
     * @param string $ip
     * @param bool $isLoopback
     * @return void
     */
    public function isLoopback($ip, $isLoopback)
    {
        $ip = new IP($ip);
        $this->assertEquals($isLoopback, $ip->isLoopback());
    }

    /**
     * Data Provider: Multicast IP Addresses
     *
     * @access public
     * @return array
     */
    public function multiCastIpAddresses()
    {
        return array(
            array('223.255.255.255', false),
            array('224.0.0.0', true),
            array('239.255.255.255', true),
            array('240.0.0.0', false),
            array('feff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', false),
            array('ff00::', true),
            array('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', true),
        );
    }

    /**
     * Test: Is multicast
     *
     * @test
     * @dataProvider multiCastIpAddresses
     * @param string $ip
     * @param bool $isMulticast
     * @return void
     */
    public function isMulticast($ip, $isMulticast)
    {
        $ip = new IP($ip);
        $this->assertEquals($isMulticast, $ip->isMulticast());
    }

    /**
     * Data Provider: Link Local IP Addresses
     *
     * @access public
     * @return array
     */
    public function unspecifiedIpAddresses()
    {
        return array(
            array('0.0.0.0'),
            array('::0'),
        );
    }

    /**
     * Test: Is unspecified
     *
     * @test
     * @dataProvider unspecifiedIpAddresses
     * @param string $ip
     * @return void
     */
    public function isUnspecified($ip)
    {
        $ip = new IP($ip);
        $this->assertTrue($ip->isUnspecified());
    }

    /**
     * Data Provider: Private-Use IP Addresses
     *
     * @access public
     * @return array
     */
    public function privateUseIpAddresses()
    {
        return array(
            array('9.255.255.255', false),
            array('10.0.0.0', true),
            array('10.255.255.255', true),
            array('11.0.0.0', false),
            array('172.15.255.255', false),
            array('172.16.0.0', true),
            array('172.31.255.255', true),
            array('172.32.0.0', false),
            array('192.167.255.255', false),
            array('192.168.0.0', true),
            array('192.168.255.255', true),
            array('192.169.0.0', false),
            array('fcff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', false),
            array('fd00::', true),
            array('fdff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', true),
            array('fe00::', false),
        );
    }

    /**
     * Test: Is private use
     *
     * @test
     * @dataProvider privateUseIpAddresses
     * @param string $ip
     * @param bool $isPrivateUse
     * @return void
     */
    public function isPrivateUse($ip, $isPrivateUse)
    {
        $ip = new IP($ip);
        $this->assertEquals($isPrivateUse, $ip->isPrivateUse());
    }
}
