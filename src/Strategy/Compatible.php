<?php

namespace Darsyn\IP\Strategy;

use Darsyn\IP\Exception\Strategy as StrategyException;

class Compatible extends AbstractStrategy
{
    /**
     * {@inheritDoc}
     */
    public function isEmbedded($binary)
    {
        return $this->getBinaryLength($binary) === 16
            && \substr($binary, 0, 12) === "\0\0\0\0\0\0\0\0\0\0\0\0";
    }

    /**
     * {@inheritDoc}
     */
    public function extract($binary)
    {
        if ($this->getBinaryLength($binary) === 16) {
            return \substr($binary, 12, 4);
        }
        throw new StrategyException\ExtractionException($binary, $this);
    }

    /**
     * {@inheritDoc}
     */
    public function pack($binary)
    {
        if ($this->getBinaryLength($binary) === 4) {
            return "\0\0\0\0\0\0\0\0\0\0\0\0" . $binary;
        }
        throw new StrategyException\PackingException($binary, $this);
    }
}
