<?php

declare(strict_types=1);

namespace Darsyn\IP\Strategy;

use Darsyn\IP\Exception\Strategy as StrategyException;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Util\MbString;

/**
 * Embeds an IPv4 address within the "Well-Known Prefix" `64:ff9b::/96`, as
 * defined by RFC 6052 ("IPv6 Addressing of IPv4/IPv6 Translators"), § 2.1
 *
 * Globally reachable, but not reserved by protocol. RFC 6052 § 3.1 states that
 * non-global IPv4 addresses must NOT be embedded as NAT64.
 */
class Nat64 implements EmbeddingStrategyInterface
{
    public function isEmbedded(string $binary): bool
    {
        return 16 === MbString::getLength($binary)
            && MbString::subString($binary, 0, 12) === Binary::fromHex('0064ff9b0000000000000000');
    }

    public function extract(string $binary): string
    {
        if (16 === MbString::getLength($binary)) {
            return MbString::subString($binary, 12, 4);
        }
        throw new StrategyException\ExtractionException($binary, $this);
    }

    public function pack(string $binary): string
    {
        // Note: non-global IPv4 addresses should not be packed into NAT64, but
        // is not enforced here. It is down to the user of this library to know
        // when to use which embedding strategy.
        if (4 === MbString::getLength($binary)) {
            return Binary::fromHex('0064ff9b0000000000000000') . $binary;
        }
        throw new StrategyException\PackingException($binary, $this);
    }
}
