<?php

declare(strict_types=1);

namespace Darsyn\IP\Strategy;

use Darsyn\IP\Exception\Strategy as StrategyException;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Util\MbString;
use Darsyn\IP\Version\IPv6;

/**
 * Embeds an IPv4 address within an IPv4-embedded IPv6 prefix, as defined by
 * RFC 6052 ("IPv6 Addressing of IPv4/IPv6 Translators"), § 2.2.
 *
 * Instances are created through named constructors only: `wellKnown()` for the
 * "Well-Known Prefix" `64:ff9b::/96` (§ 2.1); `networkSpecific()` for a
 * Network-Specific Prefix from an operator's own unicast space, using any of
 * the six prefix lengths permitted by § 2.2 (32, 40, 48, 56, 64, or 96 bits);
 * and `localUse()` for the local-use prefix `64:ff9b:1::/48` (RFC 8215). For
 * every prefix length except 96 the embedded IPv4 address is interrupted by
 * the reserved octet (bits 64 to 71), which must always be zero.
 *
 * Note: `isEmbedded()` detects any address within the configured prefix;
 * receivers decide whether an address embeds an IPv4 address by prefix
 * membership alone (RFC 6146 § 3.5). The canonical address has the reserved
 * octet and the suffix bits zeroed, whereas non-canonical addresses carry
 * other values in them: RFC 6052 § 2.2 says translators should ignore
 * non-zero suffix bits, and the extraction algorithm (§ 2.3) never inspects
 * the reserved octet.
 *
 * Consequently, version-aware operations on Multi-type IPs treat every
 * address under the configured prefix as an embedded IPv4 address, even
 * though only the canonical form can round-trip through extraction.
 *
 * The Well-Known Prefix is globally reachable, but not reserved by protocol.
 * RFC 6052 § 3.1 states that non-global IPv4 addresses must NOT be embedded
 * within the Well-Known Prefix; the restriction does not apply to
 * Network-Specific Prefixes. This library deliberately does not enforce it.
 */
class Nat64 implements EmbeddingStrategyInterface
{
    /** Hex representation of the Well-Known Prefix `64:ff9b::/96` (RFC 6052 § 2.1). */
    public const WELL_KNOWN_PREFIX = '0064ff9b000000000000000000000000';

    /** Hex representation of the local-use prefix `64:ff9b:1::/48` (RFC 8215). */
    public const LOCAL_USE_PREFIX = '0064ff9b000100000000000000000000';

    /** Prefix lengths permitted by RFC 6052 § 2.2. */
    public const PREFIX_LENGTHS = [32, 40, 48, 56, 64, 96];

    /** @var string $prefix */
    private $prefix;

    /** @var int $length */
    private $length;

    private function __construct(string $prefix, int $length)
    {
        if (16 !== $bytes = MbString::getLength($prefix)) {
            throw new \InvalidArgumentException(sprintf(
                'NAT64 embedding strategy requires an IPv6 binary (16 bytes) as prefix; got %d bytes.',
                $bytes
            ));
        }
        $this->prefix = $prefix;
        $this->length = $length;
    }

    /**
     * Named constructor for the Well-Known Prefix `64:ff9b::/96`
     * (RFC 6052 § 2.1).
     */
    public static function wellKnown(): self
    {
        return new self(Binary::fromHex(self::WELL_KNOWN_PREFIX), 96);
    }

    /**
     * Named constructor for a Network-Specific Prefix from an operator's own
     * unicast space, eg `Nat64::networkSpecific(IPv6::factory('2001:db8:122:344::'), 64)`.
     *
     * @throws \InvalidArgumentException
     */
    public static function networkSpecific(IPv6 $prefix, int $length): self
    {
        if (!\in_array($length, self::PREFIX_LENGTHS, true)) {
            throw new \InvalidArgumentException(\sprintf(
                'NAT64 prefixes must be 32, 40, 48, 56, 64, or 96 bits long (RFC 6052 § 2.2); got %d.',
                $length
            ));
        }
        // Calculate the network address. However, $prefix could be any class that extends IPv6 (including
        // Multi, which would then use its own embedding strategy when calculating the network address).
        // Don't use `IPv6::factory()->getNetworkIp()` because:
        // (a) it's unnecessary computation, checking both binary and ASCII formats, and
        // (b) could accidentally read a 16-byte binary sequence as a
        //     `[a-z0-9:]{16}` ASCII address.
        $prefix = MbString::padString(MbString::subString($prefix->getBinary(), 0, \intdiv($length, 8)), 16, "\0");
        return new self($prefix, $length);
    }

    /**
     * Named constructor for the local-use prefix `64:ff9b:1::/48` (RFC 8215),
     * applying RFC 6052 § 2.2 /48 positioning (RFC 8215 defines only the
     * prefix, not an embedding layout); narrower local-use prefixes go through
     * `networkSpecific()`.
     */
    public static function localUse(): self
    {
        return new self(Binary::fromHex(self::LOCAL_USE_PREFIX), 48);
    }

    /**
     * The network address of the configured prefix as a 16-byte binary
     * sequence; bits beyond getPrefixLength() are always zero.
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getPrefixLength(): int
    {
        return $this->length;
    }

    public function isEmbedded(string $binary): bool
    {
        // Receivers decide whether an address embeds an IPv4 address by
        // prefix membership alone (RFC 6146 § 3.5); the values of the
        // reserved octet and the suffix do not affect detection.
        $bytes = \intdiv($this->length, 8);
        return 16 === MbString::getLength($binary)
            && MbString::subString($this->prefix, 0, $bytes) === MbString::subString($binary, 0, $bytes);
    }

    /**
     * Warning: extraction is positional (RFC 6052 § 2.3) and does not verify
     * that the address lies within the configured prefix, nor that the
     * reserved octet (bits 64 to 71) and the suffix are zero; when the suffix
     * bits are not zero, RFC 6052 § 2.2 says translators should ignore them
     * and proceed as if they were zero.
     */
    public function extract(string $binary): string
    {
        if (16 !== MbString::getLength($binary)) {
            throw new StrategyException\ExtractionException($binary, $this);
        }
        $bytes = \intdiv($this->length, 8);
        if (96 === $this->length) {
            return MbString::subString($binary, $bytes, 4);
        }
        // The reserved octet (bits 64 to 71) interrupts the embedded IPv4
        // address for every prefix length other than 96; stitch the address
        // back together from either side of it.
        $split = 8 - $bytes;
        return MbString::subString($binary, $bytes, $split)
            . MbString::subString($binary, 9, 4 - $split);
    }

    /**
     * Warning: packing is not the inverse of extraction for every address
     * that `isEmbedded()` accepts. Extraction keeps only the embedded IPv4
     * address; the reserved octet (bits 64 to 71) and the suffix are
     * zero-filled, so a direct pass-through (extract-pack) of a non-canonical
     * address reconstructs the canonical form, not the original.
     */
    public function pack(string $binary): string
    {
        // Note: non-global IPv4 addresses should not be packed into the
        // Well-Known Prefix (the restriction does not apply to
        // Network-Specific Prefixes), but is not enforced here. It is down to
        // the user of this library to know when to use which embedding
        // strategy.
        if (4 !== MbString::getLength($binary)) {
            throw new StrategyException\PackingException($binary, $this);
        }
        $bytes = \intdiv($this->length, 8);
        $prefix = MbString::subString($this->prefix, 0, $bytes);
        if (96 === $this->length) {
            return $prefix . $binary;
        }
        // The reserved octet (bits 64 to 71) must be zero (RFC 6052 § 2.2),
        // and the suffix should be zero; zero-fill both.
        $split = 8 - $bytes;
        $withoutSuffix = $prefix
            . MbString::subString($binary, 0, $split)
            . "\0"
            . MbString::subString($binary, $split);
        return MbString::padString($withoutSuffix, 16, "\0");
    }
}
