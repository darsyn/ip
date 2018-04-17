<?php

namespace Darsyn\IP\Strategy;

use Darsyn\IP\Exception\Strategy as StrategyException;

class Mapped extends AbstractStrategy
{
    /**
     * {@inheritDoc}
     */
    public function isEmbedded($binary)
    {
        return $this->getBinaryLength($binary) === 16
            && substr($binary, 0, 12) === pack('H*', '00000000000000000000ffff');
    }

    /**
     * {@inheritDoc}
     */
    public function extract($binary)
    {
        if ($this->getBinaryLength($binary) === 16) {
            return substr($binary, 12, 4);
        }
        throw new StrategyException\ExtractionException($binary, $this);
    }

    /**
     * {@inheritDoc}
     */
    public function pack($binary)
    {
        if ($this->getBinaryLength($binary) === 4) {
            return pack('H*', '00000000000000000000ffff') . $binary;
        }
        throw new StrategyException\PackingException($binary, $this);
    }
}
