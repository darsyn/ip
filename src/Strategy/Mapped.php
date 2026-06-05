<?php

declare(strict_types=1);

namespace Darsyn\IP\Strategy;

use Darsyn\IP\Exception\Strategy as StrategyException;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Util\MbString;

/**
 * Embeds an IPv4 address within the IPv4-mapped prefix `::ffff:0:0/96`, as
 * defined by RFC 4291 ("IP Version 6 Addressing Architecture"), § 2.5.5.2
 *
 * Reserved by protocol, and not globally reachable (as an IPv6 address).
 */
class Mapped implements EmbeddingStrategyInterface
{
    public function isEmbedded(string $binary): bool
    {
        return 16 === MbString::getLength($binary)
            && MbString::subString($binary, 0, 12) === Binary::fromHex('00000000000000000000ffff');
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
        if (4 === MbString::getLength($binary)) {
            return Binary::fromHex('00000000000000000000ffff') . $binary;
        }
        throw new StrategyException\PackingException($binary, $this);
    }
}
