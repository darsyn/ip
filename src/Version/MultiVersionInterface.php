<?php declare(strict_types=1);

namespace Darsyn\IP\Version;

use Darsyn\IP\Strategy\EmbeddingStrategyInterface;

interface MultiVersionInterface extends Version4Interface, Version6Interface
{
    /**
     * Set the default embedding strategy to be used for all new instances of
     * this class that do not specify their own embedding strategy.
     */
    public static function setDefaultEmbeddingStrategy(EmbeddingStrategyInterface $strategy): void;

    /**
     * Converts an IP address into the smallest protocol notation it can;
     * dot-notation for IPv4, and compacted (double colons) notation for IPv6.
     * Only IPv4 addresses according to the embedding strategy used will be
     * returned in dot-notation.
     */
    public function getProtocolAppropriateAddress(): string;
}
