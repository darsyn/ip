<?php

declare(strict_types=1);

namespace Darsyn\IP\Version;

use Darsyn\IP\AbstractIP;
use Darsyn\IP\Exception;
use Darsyn\IP\Strategy\EmbeddingStrategyInterface;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Util\MbString;

/**
 * IPv6 Address
 *
 * IPv6 is an immutable value object for IP addresses, including some helper
 * functions for broadcast and network addresses, and whether its within the
 * range of another IP address according to a CIDR (subnet mask), etc.
 * This class deals solely with IPv6 addresses and will throw an
 * InvalidIpAddressException when IPv4 addresses are used.
 * Internally, the IP address is converted to a 16 byte binary sequence for easy
 * mathematical operations and consistency (for example, storing the IP address'
 * binary sequence in a fixed-length database column).
 *
 * @author    Zan Baldwin <hello@zanbaldwin.com>
 * @link      https://github.com/darsyn/ip
 * @copyright 2015 Zan Baldwin
 * @license   MIT/X11 <http://j.mp/mit-license>
 */
class IPv6 extends AbstractIP implements Version6Interface
{
    public static function factory(string $ip)
    {
        try {
            // Convert from protocol notation to binary sequence.
            $binary = self::getProtocolFormatter()->pton($ip);
            // If the string was not 4 bytes long, then the IP supplied was neither
            // in protocol notation or binary sequence notation. Throw an exception.
            if (16 !== MbString::getLength($binary)) {
                throw new Exception\WrongVersionException(6, 4, $ip);
            }
        } catch (Exception\IpException $e) {
            throw new Exception\InvalidIpAddressException($ip, $e);
        }
        return new static($binary);
    }

    /**
     * @throws \Darsyn\IP\Exception\InvalidIpAddressException
     * @throws \Darsyn\IP\Exception\WrongVersionException
     * @return static
     */
    public static function fromEmbedded(string $ip, ?EmbeddingStrategyInterface $strategy = null)
    {
        return new static(Multi::factory($ip, $strategy)->getBinary());
    }

    public function getExpandedAddress(): string
    {
        // Convert the 16-byte binary sequence into a hexadecimal-string
        // representation, insert a colon between every block of 4 characters,
        // and return the resulting IP address in full IPv6 protocol notation.
        $expanded = \preg_replace('/([a-fA-F0-9]{4})/', '$1:', Binary::toHex($this->getBinary()));
        return MbString::subString(\is_string($expanded) ? $expanded : '', 0, -1);
    }

    public function getCompactedAddress(): string
    {
        try {
            return self::getProtocolFormatter()->ntop($this->getBinary());
        } catch (Exception\Formatter\FormatException $e) {
            throw new Exception\IpException('An unknown error occured internally.', 0, $e);
        }
    }

    public function getVersion(): int
    {
        return 6;
    }

    public function isLinkLocal(): bool
    {
        return $this->inRange(new self(Binary::fromHex('fe800000000000000000000000000000')), 10);
    }

    public function isLoopback(): bool
    {
        return $this->inRange(new self(Binary::fromHex('00000000000000000000000000000001')), 128);
    }

    public function isMulticast(): bool
    {
        return $this->inRange(new self(Binary::fromHex('ff000000000000000000000000000000')), 8);
    }

    public function getMulticastScope(): ?int
    {
        if (!$this->isMulticast()) {
            return null;
        }
        $firstSegment = MbString::subString($this->getBinary(), 0, 2);
        return (int) hexdec(Binary::toHex($firstSegment & Binary::fromHex('000f')));
    }

    public function isPrivateUse(): bool
    {
        // Check `fc00::/7` to cover both:
        //  - `fd00::/8` (locally-assigned), and
        //  - `fc00::/8` (reserved for centrally-assigned; proposed but never standardised, now undefined).
        return $this->inRange(new self(Binary::fromHex('fc000000000000000000000000000000')), 7);
    }

    public function isUnspecified(): bool
    {
        return "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0" === $this->getBinary();
    }

    public function isBenchmarking(): bool
    {
        return $this->inRange(new self(Binary::fromHex('20010002000000000000000000000000')), 48);
    }

    public function isDocumentation(): bool
    {
        // Two blocks are reserved for documentation: `2001:db8::/32` (RFC 3849)
        // and `3fff::/20` (RFC 9637, which updates RFC 3849). Both are listed
        // as not globally reachable in the IANA special-purpose registry.
        return $this->inRange(new self(Binary::fromHex('20010db8000000000000000000000000')), 32)
            || $this->inRange(new self(Binary::fromHex('3fff0000000000000000000000000000')), 20);
    }

    /** @deprecated Use isGloballyReachable() instead. */
    public function isPublicUse(): bool
    {
        return $this->isGloballyReachable();
    }

    public function isGloballyReachable(): bool
    {
        return self::MULTICAST_GLOBAL === $this->getMulticastScope() || $this->isUnicastGlobal();
    }

    public function isUniqueLocal(): bool
    {
        return $this->inRange(new self(Binary::fromHex('fc000000000000000000000000000000')), 7);
    }

    public function isUnicast(): bool
    {
        return !$this->isMulticast();
    }

    public function isUnicastGlobal(): bool
    {
        return $this->isUnicast()
            && !$this->isLoopback()
            && !$this->isLinkLocal()
            && !$this->isUniqueLocal()
            && !$this->isUnspecified()
            // IPv4-mapped addresses (`::ffff:0:0/96`, RFC 4291 § 2.5.5.2) are
            // listed as not globally reachable in the IANA special-purpose
            // registry.
            && !$this->isMapped()
            && !$this->isDocumentation()
            && !$this->isBenchmarking()
            && !$this->isIetfProtocolAssignment()
            // The IANA special-purpose registry lists the 6to4 (derived) block
            // `2002::/16` (RFC 3056) with a globally-reachable value of "N/A"
            // rather than "true"; when in doubt, do what the Rust standard
            // library does.
            && !$this->isDerived()
            && !$this->isNat64LocalUse()
            && !$this->isDiscardOnly()
            && !$this->isDummyPrefix()
            && !$this->isSegmentRoutingSid();
    }

    /**
     * The IANA special-purpose registry lists the IETF Protocol Assignments
     * block `2001::/23` (RFC 2928) as not globally reachable "unless allowed
     * by a more specific allocation"; the allocations within it marked as
     * globally reachable are the PCP anycast address `2001:1::1/128` (RFC
     * 7723), the TURN anycast address `2001:1::2/128` (RFC 8155), the DNS-SD
     * SRP anycast address `2001:1::3/128` (RFC 9665), AMT `2001:3::/32` (RFC
     * 7450), AS112-v6 `2001:4:112::/48` (RFC 7535), ORCHIDv2 `2001:20::/28`
     * (RFC 7343), and Drone Remote ID Protocol Entity Tags `2001:30::/28` (RFC
     * 9374).
     * Everything else in the block is treated as not globally reachable;
     * including TEREDO `2001::/32` (whose globally-reachable value is "N/A")
     * and the terminated ORCHID entry `2001:10::/28`.
     */
    private function isIetfProtocolAssignment(): bool
    {
        return $this->inRange(new self(Binary::fromHex('20010000000000000000000000000000')), 23)
            && !in_array(Binary::toHex($this->getBinary()), [
                '20010001000000000000000000000001',
                '20010001000000000000000000000002',
                '20010001000000000000000000000003',
            ], true)
            && !$this->inRange(new self(Binary::fromHex('20010003000000000000000000000000')), 32)
            && !$this->inRange(new self(Binary::fromHex('20010004011200000000000000000000')), 48)
            && !$this->inRange(new self(Binary::fromHex('20010020000000000000000000000000')), 28)
            && !$this->inRange(new self(Binary::fromHex('20010030000000000000000000000000')), 28);
    }

    /**
     * The IANA special-purpose registry lists `64:ff9b:1::/48` as not globally
     * reachable.
     */
    private function isNat64LocalUse(): bool
    {
        // RFC 8215 reserves the /48 block for local use, but operators
        // subdivide it into Network-Specific Prefixes of any RFC 6052 § 2.2
        // length that fits within a /48 (ie, /48, /56, /64, or /96), and each
        // length places the IPv4 bytes at a different offset when embedding.
        return $this->inRange(new self(Binary::fromHex('0064ff9b000100000000000000000000')), 48);
    }

    /**
     * The IANA special-purpose registry lists the Discard-Only block `100::/64`
     * (RFC 6666) as not globally reachable.
     */
    private function isDiscardOnly(): bool
    {
        return $this->inRange(new self(Binary::fromHex('01000000000000000000000000000000')), 64);
    }

    /**
     * The IANA special-purpose registry lists the Dummy Prefix `100:0:0:1::/64`
     * (RFC 9780) as not globally reachable.
     */
    private function isDummyPrefix(): bool
    {
        return $this->inRange(new self(Binary::fromHex('01000000000000010000000000000000')), 64);
    }

    /**
     * The IANA special-purpose registry lists the Segment Routing (SRv6) SID
     * block `5f00::/16` (RFC 9602) as not globally reachable.
     */
    private function isSegmentRoutingSid(): bool
    {
        return $this->inRange(new self(Binary::fromHex('5f000000000000000000000000000000')), 16);
    }

    public function __toString(): string
    {
        return $this->getCompactedAddress();
    }
}
