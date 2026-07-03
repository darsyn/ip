<?php

declare(strict_types=1);

namespace Darsyn\IP\Version;

use Darsyn\IP\AbstractIP;
use Darsyn\IP\Exception;
use Darsyn\IP\Strategy;
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
    /** @deprecated Use fromProtocol() or fromBinary() instead. */
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

    public static function fromProtocol(string $ip)
    {
        try {
            $binary = self::getProtocolFormatter()->pton($ip);
        } catch (Exception\IpException $e) {
            throw new Exception\InvalidIpAddressException($ip, $e);
        }
        // (see rant in IPv4::fromProtocol).
        if ($binary === $ip) {
            throw new Exception\InvalidIpAddressException($ip);
        }
        if (16 !== MbString::getLength($binary)) {
            throw new Exception\WrongVersionException(6, 4, $ip);
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
        if (16 !== MbString::getLength($binary)) {
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

    public static function fromIntegerString(string $integer)
    {
        try {
            $binary = Binary::fromDecimalString($integer, 16);
        } catch (\InvalidArgumentException|Exception\OverflowException $e) {
            throw new Exception\InvalidIpAddressException($integer, $e);
        }
        return static::fromBinary($binary);
    }

    public static function isValid(string $ip): bool
    {
        return null !== static::tryFromProtocol($ip);
    }

    /**
     * @throws \Darsyn\IP\Exception\InvalidIpAddressException
     * @throws \Darsyn\IP\Exception\WrongVersionException
     * @return static
     * TODO: Deprecate method after I've decided on the whole
     *       canonical/non-canonical packing situation. Will replace with
     *       IPv6::fromEmbed(Version4Interface, ?EmbeddingStrategyInterface).
     */
    public static function fromEmbedded(string $ip, ?EmbeddingStrategyInterface $strategy = null)
    {
        $multi = Multi::tryFromProtocol($ip, $strategy) ?? Multi::fromBinary($ip, $strategy);
        return new static($multi->getBinary());
    }

    public function isEmbeddedAccordingToStrategy(EmbeddingStrategyInterface $strategy): bool
    {
        return $strategy->isEmbedded($this->getBinary());
    }

    /** @not-deprecated IpInterface deprecated in favour of Contracts\StrategyDetectionInterface. */
    public function isMapped(): bool
    {
        return $this->isEmbeddedAccordingToStrategy(new Strategy\Mapped());
    }

    /** @not-deprecated IpInterface deprecated in favour of Contracts\StrategyDetectionInterface. */
    public function isDerived(): bool
    {
        return $this->isEmbeddedAccordingToStrategy(new Strategy\Derived());
    }

    /** @not-deprecated IpInterface deprecated in favour of Contracts\StrategyDetectionInterface. */
    public function isCompatible(): bool
    {
        return $this->isEmbeddedAccordingToStrategy(new Strategy\Compatible());
    }

    public function isNat64WellKnown(): bool
    {
        return $this->isEmbeddedAccordingToStrategy(Strategy\Nat64::wellKnown());
    }

    public function isNat64LocalUse(): bool
    {
        return $this->isEmbeddedAccordingToStrategy(Strategy\Nat64::localUse());
    }

    public function isTeredo(): bool
    {
        return $this->isEmbeddedAccordingToStrategy(new Strategy\Teredo());
    }

    public function getEmbeddedIp(?EmbeddingStrategyInterface $strategy = null): IPv4
    {
        // A null strategy falls back to Multi's global default; route through
        // Multi (whose constructor resolves it) rather than widening the
        // private default-strategy accessor.
        return Multi::fromBinary($this->getBinary(), $strategy)->getEmbeddedIp();
    }

    public function getExpandedAddress(): string
    {
        // Convert the 16-byte binary sequence into a hexadecimal-string
        // representation, insert a colon between every block of 4 characters,
        // and return the resulting IP address in full IPv6 protocol notation.
        $expanded = \preg_replace('/([a-fA-F0-9]{4})/', '$1:', Binary::toHex($this->getBinary()));
        return MbString::subString(\is_string($expanded) ? $expanded : '', 0, -1);
    }

    public function getCompactedAddress(/* ?ProtocolFormatterInterface $formatter = null */): string
    {
        try {
            return self::resolveProtocolFormatter(\func_get_args())->ntop($this->getBinary());
        } catch (Exception\Formatter\FormatException $e) {
            throw new Exception\IpException('An unknown error occurred internally.', 0, $e);
        }
    }

    public function getSegments(): array
    {
        $segments = [];
        foreach (MbString::split($this->getBinary(), 2) as $word) {
            $segments[] = (\ord($word[0]) << 8) + \ord($word[1]);
        }
        return $segments;
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
        return (int) \hexdec(Binary::toHex($firstSegment & Binary::fromHex('000f')));
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
        // A Well-Known Prefix NAT64 address (`64:ff9b::/96`, RFC 6052 § 2.1) is
        // globally reachable only if the IPv4 address it embeds is. RFC 6052
        // § 3.1 forbids embedding non-global addresses within the Well-Known
        // Prefix, but a received address is not guaranteed to obey that, so
        // classify by the embedded address rather than trusting the prefix.
        if ($this->isNat64WellKnown()) {
            return $this->getEmbeddedIp(Strategy\Nat64::wellKnown())->isGloballyReachable();
        }
        return $this->isUnicast()
            && !$this->isLoopback()
            && !$this->isLinkLocal()
            && !$this->isUniqueLocal()
            && !$this->isUnspecified()
            // IPv4-mapped addresses (`::ffff:0:0/96`, RFC 4291 § 2.5.5.2) are
            // listed as not globally reachable in the IANA special-purpose
            // registry.
            && !$this->isMapped()
            // The IANA special-purpose registry lists the 6to4 (derived) block
            // `2002::/16` (RFC 3056) with a globally-reachable value of "N/A"
            // rather than "true"; when in doubt, do what the Rust standard
            // library does.
            && !$this->isDerived()
            // The IANA special-purpose registry lists `64:ff9b:1::/48` (RFC
            // 8215) as not globally reachable. Detection is by prefix membership
            // alone, covering every Network-Specific Prefix operators subdivide
            // the /48 into.
            && !$this->isNat64LocalUse()
            && !$this->isDocumentation()
            && !$this->isBenchmarking()
            && !$this->isIetfProtocolAssignment()
            // The IANA special-purpose registry lists the Discard-Only block `100::/64`
            // (RFC 6666) as not globally reachable.
            && !$this->inRange(new self(Binary::fromHex('01000000000000000000000000000000')), 64)
            // The IANA special-purpose registry lists the Dummy Prefix `100:0:0:1::/64`
            // (RFC 9780) as not globally reachable.
            && !$this->inRange(new self(Binary::fromHex('01000000000000010000000000000000')), 64)
            // The IANA special-purpose registry lists the Segment Routing (SRv6) SID
            // block `5f00::/16` (RFC 9602) as not globally reachable.
            && !$this->inRange(new self(Binary::fromHex('5f000000000000000000000000000000')), 16);
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
            && !\in_array(Binary::toHex($this->getBinary()), [
                '20010001000000000000000000000001',
                '20010001000000000000000000000002',
                '20010001000000000000000000000003',
            ], true)
            && !$this->inRange(new self(Binary::fromHex('20010003000000000000000000000000')), 32)
            && !$this->inRange(new self(Binary::fromHex('20010004011200000000000000000000')), 48)
            && !$this->inRange(new self(Binary::fromHex('20010020000000000000000000000000')), 28)
            && !$this->inRange(new self(Binary::fromHex('20010030000000000000000000000000')), 28);
    }

    public function toString(): string
    {
        return $this->getCompactedAddress();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
