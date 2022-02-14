<?php

namespace Darsyn\IP\Strategy;

use Darsyn\IP\Exception\Strategy as StrategyException;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Util\MbString;

class Derived implements EmbeddingStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function isEmbedded($binary)
    {
        return MbString::getLength($binary) === 16
            && MbString::subString($binary, 0, 2) === Binary::fromHex('2002')
            && MbString::subString($binary, 6, 10) === "\0\0\0\0\0\0\0\0\0\0";
    }

    /**
     * {@inheritDoc}
     */
    public function extract($binary)
    {
        if (MbString::getLength($binary) === 16) {
            return MbString::subString($binary, 2, 4);
        }
        throw new StrategyException\ExtractionException($binary, $this);
    }

    /**
     * {@inheritDoc}
     */
    public function pack($binary)
    {
        if (MbString::getLength($binary) === 4) {
            return Binary::fromHex('2002') . $binary . "\0\0\0\0\0\0\0\0\0\0";
        }
        throw new StrategyException\PackingException($binary, $this);
    }
}
