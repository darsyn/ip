<?php declare(strict_types=1);

namespace Darsyn\IP\Strategy;

use Darsyn\IP\Binary;
use Darsyn\IP\Exception\Strategy as StrategyException;

class Derived implements EmbeddingStrategyInterface
{
    /** @inheritDoc */
    public function isEmbedded($binary)
    {
        return Binary::getLength($binary) === 16
            && Binary::subString($binary, 0, 2) === Binary::fromHex('2002')
            && Binary::subString($binary, 6, 10) === "\0\0\0\0\0\0\0\0\0\0";
    }

    /** @inheritDoc */
    public function extract($binary)
    {
        if (Binary::getLength($binary) === 16) {
            return Binary::subString($binary, 2, 4);
        }
        throw new StrategyException\ExtractionException($binary, $this);
    }

    /** @inheritDoc */
    public function pack($binary)
    {
        if (Binary::getLength($binary) === 4) {
            return Binary::fromHex('2002') . $binary . "\0\0\0\0\0\0\0\0\0\0";
        }
        throw new StrategyException\PackingException($binary, $this);
    }
}
