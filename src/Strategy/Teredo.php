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
 * A Teredo address encodes the tunnel server's IPv4 address (bits 32–63), flags
 * (bits 64–79), the client's obfuscated UDP port (bits 80–95, XOR'd with
 * `0xFFFF`), and the client's obfuscated public IPv4 address (bits 96–127,
 * XOR'd with `0xFFFFFFFF`). This strategy extracts the client address (the
 * address the Teredo address stands for).
 */
class Teredo implements EmbeddingStrategyInterface
{
    public function isEmbedded(string $binary): bool
    {
        return 16 === MbString::getLength($binary)
            && MbString::subString($binary, 0, 4) === Binary::fromHex('20010000');
    }

    public function extract(string $binary): string
    {
        if (16 === MbString::getLength($binary)) {
            // The client's public IPv4 sits in the last 4 bytes XOR'd with 0xFFFFFFFF.
            return MbString::subString($binary, 12, 4) ^ "\xff\xff\xff\xff";
        }
        throw new StrategyException\ExtractionException($binary, $this);
    }

    public function pack(string $binary): string
    {
        // Per RFC 4380 the embedded client address is the public, post-NAT
        // mapped address, so a non-global value is malformed by specification.
        // The same reasoning as RFC 6052 § 3.1 for NAT64.

        // Extraction-only: a Teredo address cannot be constructed from an IPv4
        // address alone (server, flags, and port are not derivable), so `pack()`
        // always throws.
        throw new StrategyException\PackingException($binary, $this);
        // It is down to the user of this library to know that you should not
        // use this embedding strategy to pack. At all.
    }
}
