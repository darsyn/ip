<?php

declare(strict_types=1);

namespace Darsyn\IP\Strategy;

use Darsyn\IP\Exception\Strategy as StrategyException;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Util\MbString;

class Mapped implements EmbeddingStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function isEmbedded(string $binary): bool
    {
        return MbString::getLength($binary) === 16
            && MbString::subString($binary, 0, 12) === Binary::fromHex('00000000000000000000ffff');
    }

    /**
     * {@inheritDoc}
     */
    public function extract(string $binary): string
    {
        if (MbString::getLength($binary) === 16) {
            return MbString::subString($binary, 12, 4);
        }
        throw new StrategyException\ExtractionException($binary, $this);
    }

    /**
     * {@inheritDoc}
     */
    public function pack(string $binary): string
    {
        if (MbString::getLength($binary) === 4) {
            return Binary::fromHex('00000000000000000000ffff') . $binary;
        }
        throw new StrategyException\PackingException($binary, $this);
    }
}
