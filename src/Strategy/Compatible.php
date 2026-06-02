<?php

declare(strict_types=1);

namespace Darsyn\IP\Strategy;

use Darsyn\IP\Exception\Strategy as StrategyException;
use Darsyn\IP\Util\MbString;

class Compatible implements EmbeddingStrategyInterface
{
    public function isEmbedded(string $binary): bool
    {
        return 16 === MbString::getLength($binary)
            && "\0\0\0\0\0\0\0\0\0\0\0\0" === MbString::subString($binary, 0, 12);
    }

    public function extract(string $binary): string
    {
        if (16 === MbString::getLength($binary)) {
            return MbString::subString($binary, 12, 4);
        }
        throw new StrategyException\ExtractionException($binary, $this);
    }

    public function pack(string $binary): string
    {
        if (4 === MbString::getLength($binary)) {
            return "\0\0\0\0\0\0\0\0\0\0\0\0" . $binary;
        }
        throw new StrategyException\PackingException($binary, $this);
    }
}
