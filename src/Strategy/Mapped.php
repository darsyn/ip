<?php

namespace Darsyn\IP\Strategy;

use Darsyn\IP\Binary;
use Darsyn\IP\Exception\Strategy as StrategyException;

class Mapped implements EmbeddingStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function isEmbedded($binary)
    {
        return Binary::getLength($binary) === 16
            && Binary::subString($binary, 0, 12) === Binary::fromHex('00000000000000000000ffff');
    }

    /**
     * {@inheritDoc}
     */
    public function extract($binary)
    {
        if (Binary::getLength($binary) === 16) {
            return Binary::subString($binary, 12, 4);
        }
        throw new StrategyException\ExtractionException($binary, $this);
    }

    /**
     * {@inheritDoc}
     */
    public function pack($binary)
    {
        if (Binary::getLength($binary) === 4) {
            return Binary::fromHex('00000000000000000000ffff') . $binary;
        }
        throw new StrategyException\PackingException($binary, $this);
    }
}
