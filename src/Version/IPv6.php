<?php

declare(strict_types=1);

namespace Darsyn\IP\Version;

use Darsyn\IP\AbstractIP;
use Darsyn\IP\Exception;
use Darsyn\IP\Strategy\Composite;
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
        // Two blocks are reserved for documentation: 2001:db8::/32 (RFC 3849)
        // and 3fff::/20 (RFC 9637, which updates RFC 3849). Both are listed as
        // not globally reachable in the IANA special-purpose registry.
        return $this->inRange(new self(Binary::fromHex('20010db8000000000000000000000000')), 32)
            || $this->inRange(new self(Binary::fromHex('3fff0000000000000000000000000000')), 20);
    }

    public function isPublicUse(): bool
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
        // An IPv6 address that embeds an IPv4 address is only globally reachable
        // if the address it actually stands for is; canonicalise before
        // classifying. The deprecated IPv4-compatible embedding is deliberately
        // excluded (see Composite::all()), so "::/96" addresses are still
        // classified as plain IPv6.
        $strategy = Composite::all();
        if ($strategy->isEmbedded($this->getBinary())) {
            return (new IPv4($strategy->extract($this->getBinary())))->isPublicUse();
        }
        return $this->isUnicast()
            && !$this->isLoopback()
            && !$this->isLinkLocal()
            && !$this->isUniqueLocal()
            && !$this->isUnspecified()
            && !$this->isDocumentation()
            // Benchmarking (2001:2::/48, RFC 5180) is listed as not globally
            // reachable in the IANA special-purpose registry; the IPv4 method
            // already excluded its benchmarking block but this was missed here.
            && !$this->isBenchmarking()
            && !$this->isNat64LocalUse()
            && !$this->isDiscardOnly()
            && !$this->isDummyPrefix()
            && !$this->isSegmentRoutingSid();
    }

    /** The IANA special-purpose registry lists `64:ff9b:1::/48` as not globally reachable. */
    private function isNat64LocalUse(): bool
    {
        // RFC 8215 reserves the /48 block for local use, but operators subdivide
        // it into Network-Specific Prefixes of any RFC 6052 length (/48, /56,
        // /64, /96…), and each length places the IPv4 bytes at a different offset
        // when embedding.
        return $this->inRange(new self(Binary::fromHex('0064ff9b000100000000000000000000')), 48);
    }

    /** The IANA special-purpose registry lists the Discard-Only block `100::/64` (RFC 6666) as not globally reachable. */
    private function isDiscardOnly(): bool
    {
        return $this->inRange(new self(Binary::fromHex('01000000000000000000000000000000')), 64);
    }

    /** The IANA special-purpose registry lists the Dummy Prefix `100:0:0:1::/64` (RFC 9780) as not globally reachable. */
    private function isDummyPrefix(): bool
    {
        return $this->inRange(new self(Binary::fromHex('01000000000000010000000000000000')), 64);
    }

    /** The IANA special-purpose registry lists the Segment Routing (SRv6) SID block `5f00::/16` (RFC 9602) as not globally reachable. */
    private function isSegmentRoutingSid(): bool
    {
        return $this->inRange(new self(Binary::fromHex('5f000000000000000000000000000000')), 16);
    }

    public function __toString(): string
    {
        return $this->getCompactedAddress();
    }
}
