<?php

namespace Darsyn\IP;

use Darsyn\IP\Exception;
use Darsyn\IP\Strategy\EmbeddingStrategyInterface;
use Darsyn\IP\Strategy\Mapped as MappedEmbeddingStrategy;

/**
 * IP Address
 *
 * IP is an immutable value object that provides several notations of the same
 * IP value, including some helper functions for broadcast and network
 * addresses, and whether its within the range of another IP address according
 * to a CIDR (subnet mask), etc.
 * Although it deals with both IPv4 and IPv6 notations, it makes no distinction
 * between the two protocol formats as it converts both of them to a 16-byte
 * binary sequence for easy mathematical operations and consistency (for
 * example, storing both IPv4 and IPv6 in the same column in a database).
 *
 * @author      Zander Baldwin <hello@zanderbaldwin.com>
 * @link        https://github.com/darsyn/ip
 * @copyright   2015 Zander Baldwin
 * @license     MIT/X11 <http://j.mp/mit-license>
 */
class IP
{
    const CIDR4TO6 = 96;
    const VERSION_4 = 4;
    const VERSION_6 = 6;

    /** @var \Darsyn\IP\Strategy\EmbeddingStrategyInterface $defaultEmbeddingStrategy */
    private static $defaultEmbeddingStrategy;

    /** @var \Darsyn\IP\Strategy\EmbeddingStrategyInterface $embeddingStrategy */
    private $embeddingStrategy;

    /** @var string $ip */
    protected $ip;

    /** @var integer $version */
    protected $version;

    /**
     * Set the default embedding strategy to be used for all new instances of
     * this class that do not specify their own embedding strategy.
     *
     * @static
     * @param \Darsyn\IP\Strategy\EmbeddingStrategyInterface $embeddingStrategy
     */
    public static function setDefaultEmbeddingStrategy(EmbeddingStrategyInterface $embeddingStrategy)
    {
        self::$defaultEmbeddingStrategy = $embeddingStrategy;
    }

    protected static function getDefaultEmbeddingStrategy()
    {
        return self::$defaultEmbeddingStrategy ?: new MappedEmbeddingStrategy;
    }

    /**
     * Constructor
     *
     * @param  string $ip
     * @throws \Darsyn\IP\Exception\InvalidIpAddressException
     */
    public function __construct($ip, EmbeddingStrategyInterface $embeddingStrategy = null)
    {
        $this->embeddingStrategy = $embeddingStrategy ?: static::getDefaultEmbeddingStrategy();
        // If the IP address has been given in protocol notation, convert it to
        // a 16-byte binary sequence.
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ip = current(unpack('a4', inet_pton($ip)));
            $ip = $this->embeddingStrategy->pack($ip);
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $ip = current(unpack('a16', inet_pton($ip)));
        }

        // If the string was not 16-bytes long, then the IP supplied was neither
        // in protocol notation or binary sequence notation. Throw an exception.
        if (!is_string($ip) || $this->getBinaryLength($ip) !== 16) {
            throw new Exception\InvalidIpAddressException($ip);
        }
        $this->ip = $ip;
    }

    /**
     * @param  string $ip
     * @return int
     */
    private function getIpLength($ip)
    {
        // Don't use strlen() directly to prevent incorrect lengths resulting
        // from null bytes.
        return strlen(bin2hex($ip)) / 2;
    }

    /**
     * Get Expanded Address
     *
     * Converts an IP (regardless of version) address into a full IPv6 address
     * (no double colons).
     * IPv4 addresses will be returned in IPv6 format according to the embedding
     * strategy used.
     *
     * @return string
     */
    public function getExpandedAddress()
    {
        // Convert the 16-byte binary sequence into a hexadecimal-string
        // representation.
        $hex = unpack('H*hex', $this->getBinary())['hex'];
        // Insert a colon between every block of 4 characters, and return the
        // resulting IP address in full IPv6 protocol notation.
        return substr(preg_replace('/([a-fA-F0-9]{4})/', '$1:', $hex), 0, -1);
    }

    /**
     * Get Compacted Address
     *
     * Converts an IP (regardless of version) into a compacted IPv6 address
     * (including double-colons if appropriate).
     * IPv4 addresses will be returned in IPv6 format according to the embedding
     * strategy used.
     *
     * @return string
     */
    public function getCompactedAddress()
    {
        return inet_ntop(pack('A16', $this->getBinary()));
    }

    /**
     * Get Protocol-appropriate Address
     *
     * Converts an IP address into the smallest protocol notation it can;
     * dot-notation for IPv4, and compacted (double colons) notation for IPv6.
     * Only IPv4 addresses according to the embedding strategy used will be
     * returned in dot-notation.
     *
     * @return string
     */
    public function getProtocolAppropriateAddress()
    {
        $ip = $this->getBinary();
        // If the binary string contains an embedded IPv4 address, then extract
        // it.
        if ($this->embeddingStrategy->isEmbedded($ip)) {
            $ip = $this->embeddingStrategy->extract($ip);
        }
        // Render the IP address in the correct notation according to its
        // protocol (based on how long the binary string is).
        return inet_ntop(pack('A' . $this->getIpLength($ip), $ip));
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
     * @throws \Darsyn\IP\Exception\InvalidCidrException
     * @return string
     */
    protected function getMask($cidr)
    {
        if (!is_int($cidr) || $cidr < 0 || $cidr > 128) {
            throw new Exception\InvalidCidrException($cidr);
        }
        // Since it takes 4 bits per hexadecimal, how many sections of complete
        // 1's do we have (f's)?
        $mask = str_repeat('f', floor($cidr / 4));
        // Now we have less than four 1 bits left we need to determine what
        // hexadecimal character should be added next. Of course, we should only
        // add them in there are 1 bits leftover to prevent going over the
        // 128-bit limit.
        if ($bits = $cidr % 4) {
            // Create a string representation of a 4-bit binary sequence
            // beginning with the amount of leftover 1's.
            $bin = str_pad(str_repeat('1', $bits), 4, '0', STR_PAD_RIGHT);
            // Convert that 4-bit binary string into a hexadecimal character,
            // and append it to the mask.
            $mask .= dechex(bindec($bin));
        }
        // Fill the rest of the string up with zero's to pad it out to the
        // correct length.
        $mask = str_pad($mask, 32, '0', STR_PAD_RIGHT);
        // Pack the hexadecimal sequence into a real, 16-byte binary sequence.
        $mask = pack('H*', $mask);
        return $mask;
    }

    /**
     * Get Network Address of IP
     *
     * @param  integer $cidr
     * @throws \Darsyn\IP\Exception\InvalidCidrException
     * @return \Darsyn\IP\IP
     */
    public function getNetworkIp($cidr)
    {
        // Providing that the CIDR is valid, bitwise AND the IP address binary
        // sequence with the mask generated from the CIDR.
        return new static(
            $this->getBinary() & $this->getMask($cidr),
            clone $this->embeddingStrategy
        );
    }

    /**
     * Get Broadcast Address
     *
     * @param  integer $cidr
     * @throws \Darsyn\IP\Exception\InvalidCidrException
     * @return \Darsyn\IP\IP
     */
    public function getBroadcastIp($cidr)
    {
        // Providing that the CIDR is valid, bitwise OR the IP address binary
        // sequence with the inverse of the mask generated from the CIDR.
        return new static(
            $this->getBinary() | ~$this->getMask($cidr),
            clone $this->embeddingStrategy
        );
    }

    /**
     * Is IP Address In Range?
     *
     * Returns a boolean value depending on whether the IP address in question
     * is within the range of the target IP/CIDR combination.
     *
     * @param  \Darsyn\IP\IP $ip
     * @param  integer $cidr
     * @throws \Darsyn\IP\Exception\InvalidCidrException
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
            $this->version = $this->embeddingStrategy->isEmbedded($this->getBinary())
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
        return (new Strategy\Mapped)->isEmbedded($this->getBinary());
    }

    /**
     * Whether the IP is a 6to4-derived address (eg, "2002:7f00:1::").
     *
     * @return bool
     */
    public function isDerived()
    {
        return (new Strategy\Derived)->isEmbedded($this->getBinary());
    }

    /**
     * Whether the IP is an IPv4-compatible IPv6 address (eg, `::7f00:1`).
     *
     * @return bool
     */
    public function isCompatible()
    {
        return (new Strategy\Compatible)->isEmbedded($this->getBinary());
    }

    /**
     * Whether the IP is an IPv4-embedded IPv6 address (either a mapped or
     * compatible address).
     *
     * @return bool
     */
    public function isEmbedded()
    {
        return $this->embeddingStrategy->isEmbedded($this->getBinary());
    }

    /**
     * Whether the IP is reserved for link-local usage according to
     * RFC 3927/RFC 4291 (IPv4/IPv6).
     *
     * @return bool
     */
    public function isLinkLocal()
    {
        return $this->inRange(new static('169.254.0.0', clone $this->embeddingStrategy), self::CIDR4TO6 + 16)
            || $this->inRange(new static('fe80::', clone $this->embeddingStrategy), 10);
    }

    /**
     * Whether the IP is a loopback address according to RFC 2373/RFC 3330
     * (IPv4/IPv6).
     *
     * @return bool
     */
    public function isLoopback()
    {
        return $this->inRange(new static('127.0.0.0', clone $this->embeddingStrategy), self::CIDR4TO6 + 8)
            || $this->inRange(new static('::1', clone $this->embeddingStrategy), 128);
    }

    /**
     * Whether the IP is a multicast address according to RFC 3171/RFC 2373
     * (IPv4/IPv6).
     *
     * @return bool
     */
    public function isMulticast()
    {
        return $this->inRange(new static('224.0.0.0', clone $this->embeddingStrategy), self::CIDR4TO6 + 4)
            || $this->inRange(new static('ff00::', clone $this->embeddingStrategy), 8);
    }

    /**
     * Whether the IP is for private use according to RFC 1918/RFC 4193
     * (IPv4/IPv6).
     *
     * @return bool
     */
    public function isPrivateUse()
    {
        return $this->inRange(new static('10.0.0.0', clone $this->embeddingStrategy), self::CIDR4TO6 + 8)
            || $this->inRange(new static('172.16.0.0', clone $this->embeddingStrategy), self::CIDR4TO6 + 12)
            || $this->inRange(new static('192.168.0.0', clone $this->embeddingStrategy), self::CIDR4TO6 + 16)
            || $this->inRange(new static('fd00::', clone $this->embeddingStrategy), 8);
    }

    /**
     * Whether the IP is unspecified according to RFC 5735/RFC 2373 (IPv4/IPv6).
     *
     * @return bool
     */
    public function isUnspecified()
    {
        return $this->getBinary() === "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
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
