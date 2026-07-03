<?php

declare(strict_types=1);

namespace Darsyn\IP\Version;

use Darsyn\IP\AbstractIP;
use Darsyn\IP\Exception;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Util\MbString;

/**
 * IPv4 Address
 *
 * IPv4 is an immutable value object for IP addresses, including some helper
 * functions for broadcast and network addresses, and whether its within the
 * range of another IP address according to a CIDR (subnet mask), etc.
 * This class deals solely with IPv4 addresses and will throw an
 * InvalidIpAddressException when IPv6 addresses are used.
 * Internally, the IP address is converted to a 4 byte binary sequence for easy
 * mathematical operations and consistency (for example, storing the IP address'
 * binary sequence in a fixed-length database column).
 *
 * @author    Zan Baldwin <hello@zanbaldwin.com>
 * @link      https://github.com/darsyn/ip
 * @copyright 2015 Zan Baldwin
 * @license   MIT/X11 <http://j.mp/mit-license>
 */
class IPv4 extends AbstractIP implements Version4Interface
{
    /** @deprecated Use fromProtocol() or fromBinary() instead. */
    public static function factory(string $ip)
    {
        try {
            // Convert from protocol notation to binary sequence.
            $binary = self::getProtocolFormatter()->pton($ip);
            // If the string was not 4 bytes long, then the IP supplied was
            // neither in protocol notation or binary sequence notation. Throw
            // an exception.
            if (4 !== MbString::getLength($binary)) {
                if (4 !== MbString::getLength($ip)) {
                    throw new Exception\WrongVersionException(4, 6, $ip);
                }
                $binary = $ip;
            }
        } catch (Exception\IpException $e) {
            throw new Exception\InvalidIpAddressException($ip, $e);
        }
        return new static($binary);
    }

    public static function fromProtocol(string $ip)
    {
        try {
            $binary = self::getProtocolFormatter()->pton($ip);
        } catch (Exception\IpException $e) {
            throw new Exception\InvalidIpAddressException($ip, $e);
        }
        // pton() returns a raw 4/16-byte string verbatim as a permissive
        // fallback (the behaviour the old factory relies on); strict protocol
        // parsing must reject anything not actually parsed from protocol notation.
        // TODO: This _really_ needs to change before the next major version bump.
        if ($binary === $ip) {
            throw new Exception\InvalidIpAddressException($ip);
        }
        if (4 !== MbString::getLength($binary)) {
            throw new Exception\WrongVersionException(4, 6, $ip);
        }
        return new static($binary);
    }

    public static function tryFromProtocol(string $ip)
    {
        try {
            return static::fromProtocol($ip);
        } catch (Exception\InvalidIpAddressException $e) {
            return null;
        }
    }

    public static function fromBinary(string $binary)
    {
        if (4 !== MbString::getLength($binary)) {
            throw new Exception\InvalidBinaryException($binary);
        }
        return new static($binary);
    }

    public static function tryFromBinary(string $binary)
    {
        try {
            return static::fromBinary($binary);
        } catch (Exception\InvalidIpAddressException $e) {
            return null;
        }
    }

    public static function fromHex(string $hex)
    {
        try {
            $binary = Binary::fromHex($hex);
        } catch (\InvalidArgumentException $e) {
            throw new Exception\InvalidIpAddressException($hex, $e);
        }
        return static::fromBinary($binary);
    }

    public static function tryFromHex(string $hex)
    {
        try {
            return static::fromHex($hex);
        } catch (Exception\InvalidIpAddressException $e) {
            return null;
        }
    }

    public static function fromInteger(int $integer)
    {
        if ($integer < 0 || $integer > 0xffffffff) {
            throw new Exception\InvalidIpAddressException($integer);
        }
        return static::fromBinary(
            \chr($integer >> 24 & 0xff)
            . \chr($integer >> 16 & 0xff)
            . \chr($integer >> 8 & 0xff)
            . \chr($integer & 0xff)
        );
    }

    public static function isValid(string $ip): bool
    {
        return null !== static::tryFromProtocol($ip);
    }

    public function getDotAddress(/* ?ProtocolFormatterInterface $formatter = null */): string
    {
        try {
            return self::resolveProtocolFormatter(\func_get_args())->ntop($this->getBinary());
        } catch (Exception\Formatter\FormatException $e) {
            throw new Exception\IpException('An unknown error occurred internally.', 0, $e);
        }
    }

    /** @return int<0, 4294967295> */
    public function toInteger(): int
    {
        $binary = $this->getBinary();
        return (\ord($binary[0]) << 24)
            + (\ord($binary[1]) << 16)
            + (\ord($binary[2]) << 8)
            + \ord($binary[3]);
    }

    public function getVersion(): int
    {
        return 4;
    }

    public function isLinkLocal(): bool
    {
        return $this->inRange(new self(Binary::fromHex('a9fe0000')), 16);
    }

    public function isLoopback(): bool
    {
        return $this->inRange(new self(Binary::fromHex('7f000000')), 8);
    }

    public function isMulticast(): bool
    {
        return $this->inRange(new self(Binary::fromHex('e0000000')), 4);
    }

    public function isPrivateUse(): bool
    {
        return $this->inRange(new self(Binary::fromHex('0a000000')), 8)
            || $this->inRange(new self(Binary::fromHex('ac100000')), 12)
            || $this->inRange(new self(Binary::fromHex('c0a80000')), 16);
    }

    public function isUnspecified(): bool
    {
        return "\0\0\0\0" === $this->getBinary();
    }

    public function isBenchmarking(): bool
    {
        return $this->inRange(new self(Binary::fromHex('c6120000')), 15);
    }

    public function isDocumentation(): bool
    {
        // The three TEST-NET blocks (RFC 5737), plus MCAST-TEST-NET
        // 233.252.0.0/24 (RFC 5771 § 9.2), which is assigned for use in
        // documentation and example code and MUST NOT appear on the public
        // Internet.
        return $this->inRange(new self(Binary::fromHex('c0000200')), 24)
            || $this->inRange(new self(Binary::fromHex('c6336400')), 24)
            || $this->inRange(new self(Binary::fromHex('cb007100')), 24)
            || $this->inRange(new self(Binary::fromHex('e9fc0000')), 24);
    }

    /** @deprecated Use isGloballyReachable() instead. */
    public function isPublicUse(): bool
    {
        return $this->isGloballyReachable();
    }

    public function isGloballyReachable(): bool
    {
        // The PCP anycast address `192.0.0.9` (RFC 7723) and the TURN anycast
        // address `192.0.0.10` (RFC 8155) are globally routable, despite being
        // within the IETF Protocol Assignments block.
        if (\in_array(Binary::toHex($this->getBinary()), ['c0000009', 'c000000a'], true)) {
            return true;
        }
        // The whole "this network" block `0.0.0.0/8` (RFC 791 § 3.2) is not
        // globally reachable.
        if ($this->inRange(new self(Binary::fromHex('00000000')), 8)) {
            return false;
        }
        // Addresses reserved for future protocols are not globally routable.
        // The IETF Protocol Assignments block `192.0.0.0/24` (RFC 6890 § 2.1)
        // is different to "reserved for future use".
        if ($this->inRange(new self(Binary::fromHex('c0000000')), 24)) {
            return false;
        }
        // The `6a44`-relay anycast address `192.88.99.2/32` (RFC 6751) is
        // listed as not globally reachable. Note that the surrounding 6to4
        // Relay Anycast block `192.88.99.0/24` was deprecated by RFC 7526 and
        // its registry entry terminated (2015-03) with every attribute column
        // left blank and is NOT listed as "globally reachable: false".
        // The rest of that block falls through as globally reachable (when in
        // doubt, do what the Rust standard library does).
        if ($this->getBinary() === Binary::fromHex('c0586302')) {
            return false;
        }

        // Note: IPv4 multicast (`224.0.0.0/4`) is deliberately NOT excluded
        // here. It is absent from the IANA IPv4 special-purpose address
        // registry (which defines "globally reachable"). Unlike IPv6, IPv4
        // multicast carries no in-address scope field so it cannot be
        // scope-classified the way IPv6 multicast is via `getMulticastScope()`.
        // > `239.0.0.0/8` is administratively scoped (RFC 2365), configured at
        // > boundary routers rather than encoded in the address.
        return !$this->isPrivateUse()
            && !$this->isLoopback()
            && !$this->isLinkLocal()
            && !$this->isBroadcast()
            && !$this->isShared()
            && !$this->isDocumentation()
            && !$this->isFutureReserved()
            && !$this->isBenchmarking();
    }

    public function isBroadcast(): bool
    {
        return $this->getBinary() === Binary::fromHex('ffffffff');
    }

    public function isShared(): bool
    {
        return $this->inRange(new self(Binary::fromHex('64400000')), 10);
    }

    public function isFutureReserved(): bool
    {
        // `255.255.255.255` is carved out of `240.0.0.0/4` (RFC 1112 § 4): the
        // IANA special-purpose registry lists the limited broadcast address as
        // its own entry (RFC 8190, RFC 919 § 7).
        return $this->getBinary() !== Binary::fromHex('ffffffff')
            && $this->inRange(new self(Binary::fromHex('f0000000')), 4);
    }

    public function toString(): string
    {
        return $this->getDotAddress();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
