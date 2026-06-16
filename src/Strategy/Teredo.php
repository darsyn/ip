<?php

declare(strict_types=1);

namespace Darsyn\IP\Strategy;

use Darsyn\IP\Exception\Strategy as StrategyException;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Util\MbString;

/**
 * Extracts an IPv4 address from the Teredo prefix `2001::/32`, as defined by
 * RFC 4380 ("Teredo: Tunneling IPv6 over UDP through NATs"), § 4.
 *
 * Note: `isEmbedded()` detects any address within the Teredo prefix
 * (`2001::/32`, RFC 4380 § 2.6) with the IPv4 address embedded in the last 32
 * bits, obfuscated by XOR'ing with 0xFFFFFFFF. The canonical address has bits
 * 32-95 zeroed, whereas the non-canonical form embeds the tunnel server's IPv4
 * address (bits 32-63), flags (bits 64-79), and the client's obfuscated UDP
 * port (bits 80-95, XOR'd with 0xFFFF).
 *
 * Consequently, version-aware operations on Multi-type IPs using this strategy
 * treat every Teredo address as an embedded IPv4 address, even though only the
 * canonical form can round-trip through extraction.
 */
class Teredo implements CanonicalEmbeddingInterface
{
    private const INVERSE_MASK = "\xff\xff\xff\xff";

    public function isEmbedded(string $binary): bool
    {
        return 16 === MbString::getLength($binary)
            && MbString::subString($binary, 0, 4) === Binary::fromHex('20010000');
    }

    public function extract(string $binary): string
    {
        if (16 === MbString::getLength($binary)) {
            // The client's public IPv4 sits in the last 4 bytes XOR'd with 0xFFFFFFFF.
            return MbString::subString($binary, 12, 4) ^ self::INVERSE_MASK;
        }
        throw new StrategyException\ExtractionException($binary, $this);
    }

    /**
     * Warning: packing is not the inverse of extraction for every address that
     * `isEmbedded()` accepts. Extraction keeps only the client's public IPv4
     * address carried (obfuscated) in bits 96-127.
     * The tunnel server's IPv4 address, the flags, and the obfuscated UDP port
     * are lost so a direct pass-through (extract-pack) reconstructs the
     * canonical Teredo address (`2001::XXXX:XXXX`), not the original.
     *
     * @deprecated Use packIntoCanonical() instead.
     */
    public function pack(string $binary): string
    {
        return $this->packIntoCanonical($binary);
    }

    public function packIntoCanonical(string $ipv4): string
    {
        if (4 === MbString::getLength($ipv4)) {
            // Zero the server/flags/port fields.
            $serverFlagsPort = Binary::fromHex('0000000000000000');
            // Re-obfuscate the client IPv4 (XOR 0xFFFFFFFF).
            return Binary::fromHex('20010000') . $serverFlagsPort . ($ipv4 ^ self::INVERSE_MASK);
        }
        throw new StrategyException\PackingException($ipv4, $this);
    }

    public function packIntoNonCanonical(string $ipv6, string $ipv4): string
    {
        if (!$this->isEmbedded($ipv6)) {
            throw new StrategyException\PackingException($ipv6, $this);
        }
        if (4 !== MbString::getLength($ipv4)) {
            throw new StrategyException\PackingException($ipv4, $this);
        }
        // Re-obfuscate the client IPv4 (XOR 0xFFFFFFFF with bits 96-127).
        return MbString::subString($ipv6, 0, 12) . ($ipv4 ^ self::INVERSE_MASK);
    }
}
