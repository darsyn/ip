<?php

namespace Darsyn\IP\Strategy;

use Darsyn\IP\Binary;
use Darsyn\IP\Exception\Strategy as StrategyException;

class Compatible implements EmbeddingStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function isEmbedded($binary)
    {
        return Binary::getLength($binary) === 16
            && Binary::subString($binary, 0, 12) === "\0\0\0\0\0\0\0\0\0\0\0\0";
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
            return "\0\0\0\0\0\0\0\0\0\0\0\0" . $binary;
        }
        throw new StrategyException\PackingException($binary, $this);
    }
}
