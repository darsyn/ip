<?php

namespace Darsyn\IP\Strategy;

abstract class AbstractStrategy implements EmbeddingStrategyInterface
{
    /**
     * @param string $binary
     * @return integer
     */
    protected function getBinaryLength($binary)
    {
        return strlen(bin2hex($binary)) / 2;
    }

    /**
     * @param string $hex
     * @return string
     */
    protected function getBinaryFromHex($hex)
    {
        return pack('H*', $hex);
    }
}
