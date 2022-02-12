<?php

namespace Darsyn\IP\Strategy;

use Darsyn\IP\Exception\Strategy as StrategyException;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Util\MbString;

class Mapped implements EmbeddingStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function isEmbedded($binary)
    {
        return MbString::getLength($binary) === 16
            && MbString::subString($binary, 0, 12) === Binary::fromHex('00000000000000000000ffff');
    }

    /**
     * {@inheritDoc}
     */
    public function extract($binary)
    {
        if (MbString::getLength($binary) === 16) {
            return MbString::subString($binary, 12, 4);
        }
        throw new StrategyException\ExtractionException($binary, $this);
    }

    /**
     * {@inheritDoc}
     */
    public function pack($binary)
    {
        if (MbString::getLength($binary) === 4) {
            return Binary::fromHex('00000000000000000000ffff') . $binary;
        }
        throw new StrategyException\PackingException($binary, $this);
    }
}
