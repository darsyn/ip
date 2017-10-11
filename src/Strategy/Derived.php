<?php

namespace Darsyn\IP\Strategy;

use Darsyn\IP\Exception\Strategy as StrategyException;

class Derived extends AbstractStrategy
{
    /**
     * {@inheritDoc}
     */
    public function isEmbedded($binary)
    {
        return $this->getBinaryLength($binary) === 16
            && substr($binary, 0, 2) === $this->getBinaryFromHex('2002')
            && substr($binary, 6, 10) === "\0\0\0\0\0\0\0\0\0\0\0\0";
    }

    /**
     * {@inheritDoc}
     */
    public function extract($binary)
    {
        if ($this->getBinaryLength($binary) === 16) {
            return substr($binary, 2, 4);
        }
        throw new StrategyException\ExtractionException($binary, $this);
    }

    /**
     * {@inheritDoc}
     */
    public function pack($binary)
    {
        if ($this->getBinaryLength($binary) === 4) {
            return $this->getBinaryFromHex('2002') . $binary . "\0\0\0\0\0\0\0\0\0\0";
        }
        throw new StrategyException\PackingException($binary, $this);
    }
}
