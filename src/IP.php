<?php

namespace Darsyn\IP;

/**
 * IP Address
 *
 * IP is an immutable value object that provides several notations of the same IP
 * value, including some helper functions for broadcast and network addresses,
 * and whether its within the range of another IP address according to a CIDR
 * (subnet mask).
 * Although it deals with both IPv4 and IPv6 notations, it makes no distinction
 * between the two protocol formats as it converts both of them to a 16-byte
 * binary sequence for easy mathematical operations and consistency (for example,
 * storing both IPv4 and IPv6 in the same column in a database).
 *
 * @author      Zander Baldwin <hello@zanderbaldwin.com>
 * @link        https://github.com/darsyn/ip
 * @copyright   2015 Zander Baldwin
 * @license     MIT/X11 <http://j.mp/mit-license>
 */
class IP
{
    const VERSION_4 = 4;
    const VERSION_6 = 6;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var integer
     */
    private $version;

    /**
     * Constructor
     *
     * @access public
     * @param  string $ip
     * @throws \Darsyn\IP\InvalidIpAddressException
     */
    public function __construct($ip)
    {
        // If the IP address has been given in protocol notation, convert it to
        // a 16-byte binary sequence.
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ip = current(unpack('a4', inet_pton($ip)));
            $ip = str_pad($ip, 16, "\0", STR_PAD_LEFT);
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $ip = current(unpack('a16', inet_pton($ip)));
        }
        if (!is_string($ip) || $this->getIpLength($ip) !== 16) {
            // If the string was not 16-bytes long, then the IP supplied was neither
            // in protocol notation or binary sequence notation. Throw an exception.
            throw new InvalidIpAddressException($ip);
        }
        $this->ip = $ip;
    }

    /**
     * @param  string $ip
     * @return int
     */
    private function getIpLength($ip)
    {
        return strlen(bin2hex($ip)) / 2;
    }

    /**
     * Get Short Address
     *
     * Converts an IP address into the smallest protocol notation it can; dot-notation
     * for IPv4, and compacted (double colons) notation for IPv6.
     *
     * @return string
     */
    public function getShortAddress()
    {
        // If the binary representation of the IP address begins with 12 zeros,
        // it means it's an IPv4 address. Remove the zero's first so that PHP's
        // in-built number-to-protocol function will convert it accordingly.
        $ip = preg_replace('/^\0{12}/', '', $this->getBinary());
        return inet_ntop(pack('A' . $this->getIpLength($ip), $ip));
    }

    /**
     * Get Long Address
     *
     * Converts an IP (regardless of version) address into a full IPv6 address (no
     * double colons).
     *
     * @return string
     */
    public function getLongAddress()
    {
        // Convert the 16-byte binary sequence into a hexadecimal-string representation.
        $hex = unpack('H*hex', $this->getBinary());
        // Insert a colon between every block of 4 characters, and return the
        // resulting IP address in full IPv6 protocol notation.
        return substr(preg_replace('/([a-fA-F0-9]{4})/', '$1:', $hex['hex']), 0, -1);
    }

    /**
     * Get Binary Representation
     *
     * @return string
     */
    public function getBinary()
    {
        return $this->ip;
    }

    /**
     * Subnet Mask
     *
     * Generates an IPv6 subnet mask for the CIDR value passed.
     *
     * @param  integer $cidr
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function getMask($cidr)
    {
        if (!is_int($cidr) || $cidr < 0 || $cidr > 128) {
            throw new \InvalidArgumentException('CIDR must be an integer between 0 and 128.');
        }
        // Since it takes 4 bits per hexadecimal, how many sections of complete 1's do we have (f's)?
        $mask = str_repeat('f', floor($cidr / 4));
        // Now we have less than four 1 bits left we need to determine what hexadecimal
        // character should be added next. Of course, we should only add them in
        // there are 1 bits leftover to prevent going over the 128-bit limit.
        if ($bits = $cidr % 4) {
            // Create a string representation of a 4-bit binary sequence beginning
            // with the amount of leftover 1's.
            $bin = str_pad(str_repeat('1', $bits), 4, '0', STR_PAD_RIGHT);
            // Convert that 4-bit binary string into a hexadecimal character,
            // and append it to the mask.
            $mask .= dechex(bindec($bin));
        }
        // Fill the rest of the string up with zero's to pad it out to the correct length.
        $mask = str_pad($mask, 32, '0', STR_PAD_RIGHT);
        // Pack the hexadecimal sequence into a real, 16-byte binary sequence.
        $mask = pack('H*', $mask);
        return $mask;
    }

    /**
     * Get Network Address of IP
     *
     * @param  integer $cidr
     * @throws \InvalidArgumentException
     * @return \Darsyn\IP\IP
     */
    public function getNetworkIp($cidr)
    {
        // Providing that the CIDR is valid, bitwise AND the IP address binary
        // sequence with the mask generated from the CIDR.
        return new static($this->getBinary() & $this->getMask($cidr));
    }

    /**
     * Get Broadcast Address
     *
     * @param  integer $cidr
     * @throws \InvalidArgumentException
     * @return \Darsyn\IP\IP
     */
    public function getBroadcastIp($cidr)
    {
        // Providing that the CIDR is valid, bitwise OR the IP address binary
        // sequence with the inverse of the mask generated from the CIDR.
        $mask = $this->getMask($cidr);
        return new static($this->getBinary() | ~$mask);
    }

    /**
     * Is IP Address In Range?
     *
     * Returns a boolean value depending on whether the IP address in question
     * is within the range of the target IP/CIDR combination.
     *
     * @param  \Darsyn\IP\IP $ip
     * @param  integer $cidr
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function inRange(IP $ip, $cidr)
    {
        return $this->getNetworkIp($cidr)->getBinary() === $ip->getNetworkIp($cidr)->getBinary();
    }

    /**
     * Get the IP version from the binary value
     *
     * @return integer
     */
    public function getVersion()
    {
        if ($this->version === null) {
            $this->version = strpos($binary = $this->getBinary(), str_repeat($nibble = "\0\0", 5)) === 0
                && in_array(substr($binary, 10, 2), [$nibble, ~$nibble], true)
                ? self::VERSION_4
                : self::VERSION_6;
        }
        return $this->version;
    }

    /**
     * Is Version?
     *
     * @param  integer $version
     * @return bool
     */
    public function isVersion($version)
    {
        return $this->getVersion() === $version;
    }

    /**
     * Whether the IP is version 4
     *
     * @return bool
     */
    public function isVersion4()
    {
        return $this->isVersion(self::VERSION_4);
    }

    /**
     * Whether the IP is version 6
     *
     * @return bool
     */
    public function isVersion6()
    {
        return $this->isVersion(self::VERSION_6);
    }

    /**
     * Whether the IP is an IPv4-mapped IPv6 address (eg, "::ffff:7f00:1").
     *
     * @return bool
     */
    public function isMapped()
    {
        return $this->inRange(new IP('::ffff:0:0'), 96);
    }

    /**
     * Whether the IP is a 6to4-derived address (eg, "2002:7f00:1::").
     *
     * @return bool
     */
    public function isDerived()
    {
        return substr($this->ip, 0, 2) === pack('H*', '2002')
            && substr($this->ip, 6) === "\0\0\0\0\0\0\0\0\0\0";
    }

    /**
     * Whether the IP is reserved for link-local usage according to RFC 3927/RFC 4291 (IPv4/IPv6)
     *
     * @return bool
     */
    public function isLinkLocal()
    {
        return
            $this->inRange(new IP('169.254.0.0'), 96 + 16) ||
            $this->inRange(new IP('fe80::'), 10)
        ;
    }

    /**
     * Whether the IP is a loopback address according to RFC 2373/RFC 3330 (IPv4/IPv6)
     *
     * @return bool
     */
    public function isLoopback()
    {
        return $this->inRange(new IP('127.0.0.0'), 96 + 8)
            || $this->inRange(new IP('::1'), 128);
    }

    /**
     * Whether the IP is a multicast address according to RFC 3171/RFC 2373 (IPv4/IPv6)
     *
     * @return bool
     */
    public function isMulticast()
    {
        return $this->inRange(new IP('224.0.0.0'), 96 + 4)
            || $this->inRange(new IP('ff00::'), 8);
    }

    /**
     * Whether the IP is for private use according to RFC 1918/RFC 4193 (IPv4/IPv6)
     *
     * @return bool
     */
    public function isPrivateUse()
    {
        return $this->inRange(new IP('10.0.0.0'), 96 + 8)
            || $this->inRange(new IP('172.16.0.0'), 96 + 12)
            || $this->inRange(new IP('192.168.0.0'), 96 + 16)
            || $this->inRange(new IP('fd00::'), 8);
    }

    /**
     * Whether the IP is unspecified according to RFC 5735/RFC 2373 (IPv4/IPv6)
     *
     * @return bool
     */
    public function isUnspecified()
    {
        return $this->getShortAddress() === '0.0.0.0';
    }

    /**
     * To String (Magic Method)
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getBinary();
    }
}
