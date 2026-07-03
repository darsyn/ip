<?php

declare(strict_types=1);

namespace Darsyn\IP\Version;

use Darsyn\IP\Formatter\ProtocolFormatterInterface;
use Darsyn\IP\Strategy\EmbeddingStrategyInterface;

interface MultiVersionInterface extends Version4Interface, Version6Interface
{
    /**
     * Set the default embedding strategy to be used for all new instances of
     * this class that do not specify their own embedding strategy.
     */
    public static function setDefaultEmbeddingStrategy(EmbeddingStrategyInterface $strategy): void;

    /**
     * Whether an IPv4 address is embedded within this address, according to
     * the embedding strategy in effect for this instance.
     *
     * @not-deprecated IpInterface deprecated in favour of
     *                 Contracts\StrategyDetectionInterface
     */
    public function isEmbedded(): bool;

    /** @deprecated Use toProtocolAppropriateAddress() instead. */
    public function getProtocolAppropriateAddress(): string;

    /**
     * Convert to Protocol-appropriate Address Notation
     *
     * Converts an IP address into the smallest protocol notation it can;
     * dot-notation for IPv4, and compacted (double colons) notation for IPv6.
     * Only IPv4 addresses according to the embedding strategy used will be
     * returned in dot-notation.
     */
    public function toProtocolAppropriateAddress(?ProtocolFormatterInterface $formatter = null): string;
}
