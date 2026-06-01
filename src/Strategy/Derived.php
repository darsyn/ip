<?php

declare(strict_types=1);

namespace Darsyn\IP\Strategy;

use Darsyn\IP\Exception\Strategy as StrategyException;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Util\MbString;

/**
 * Embeds an IPv4 address within the 6to4 prefix `2002::/16`, as defined by RFC
 * 3056 ("Connection of IPv6 Domains via IPv4 Clouds").
 *
 * Legacy, but not formally deprecated (only 6to4 anycast was deprecated via
 * RFC 7526).
 */
class Derived implements EmbeddingStrategyInterface
{
    public function isEmbedded(string $binary): bool
    {
        return 16 === MbString::getLength($binary)
            && MbString::subString($binary, 0, 2) === Binary::fromHex('2002')
            && "\0\0\0\0\0\0\0\0\0\0" === MbString::subString($binary, 6, 10);
    }

    public function extract(string $binary): string
    {
        if (16 === MbString::getLength($binary)) {
            return MbString::subString($binary, 2, 4);
        }
        throw new StrategyException\ExtractionException($binary, $this);
    }

    public function pack(string $binary): string
    {
        if (4 === MbString::getLength($binary)) {
            return Binary::fromHex('2002') . $binary . "\0\0\0\0\0\0\0\0\0\0";
        }
        throw new StrategyException\PackingException($binary, $this);
    }
}
