<?php

declare(strict_types=1);

namespace Darsyn\IP\Strategy;

use Darsyn\IP\Exception\Strategy as StrategyException;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Util\MbString;

/**
 * Embeds an IPv4 address within the 6to4 prefix `2002::/16`, as defined by RFC
 * 3056 ("Connection of IPv6 Domains via IPv4 Clouds") § 2.
 *
 * Note: `isEmbedded()` detects any address within the 6to4 block (`2002::/16`,
 * RFC 3056) with the IPv4 address embedded in bits 16-47. The canonical address
 * has the remaining 80 bits zeroed, whereas non-canonical addresses embed the
 * SLA ID (subnet) and interface ID.
 *
 * Consequently, version-aware operations on Multi-type IPs treat every 6to4
 * host address as an embedded IPv4 address, even though only the canonical
 * form can round-trip through extraction.
 *
 * N.B. Legacy, but not formally deprecated (only 6to4 anycast was deprecated
 * via RFC 7526).
 */
class Derived implements EmbeddingStrategyInterface
{
    public function isEmbedded(string $binary): bool
    {
        return 16 === MbString::getLength($binary)
            && MbString::subString($binary, 0, 2) === Binary::fromHex('2002');
    }

    public function extract(string $binary): string
    {
        if (16 === MbString::getLength($binary)) {
            return MbString::subString($binary, 2, 4);
        }
        throw new StrategyException\ExtractionException($binary, $this);
    }

    /**
     * Warning: packing is not the inverse of extraction for every address that
     * `isEmbedded()` accepts. Extraction keeps only the IPv4 address carried
     * in bits 16-47 of a 6to4 address.
     * The SLA ID and interface ID bits are lost so a direct pass-through
     * (extract-pack) reconstructs the canonical Derived address
     * (`2002:XXXX:XXXX::`), not the original.
     */
    public function pack(string $binary): string
    {
        if (4 === MbString::getLength($binary)) {
            // Zero the SLA ID (subnet) and interface ID fields.
            $subnetInterface = "\0\0\0\0\0\0\0\0\0\0";
            return Binary::fromHex('2002') . $binary . $subnetInterface;
        }
        throw new StrategyException\PackingException($binary, $this);
    }
}
