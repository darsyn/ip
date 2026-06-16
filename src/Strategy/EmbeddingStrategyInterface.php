<?php

declare(strict_types=1);

namespace Darsyn\IP\Strategy;

interface EmbeddingStrategyInterface
{
    /**
     * Checks if the IPv6 binary string supplied contains an IPv4 embedded
     * according to the implemented embedding strategy.
     */
    public function isEmbedded(string $binary): bool;

    /**
     * Extract the embedded IPv4 binary string from the IPv6 binary string
     * supplied, according to the implemented embedding strategy.
     *
     * @throws \Darsyn\IP\Exception\Strategy\ExtractionException
     */
    public function extract(string $binary): string;

    /**
     * Convert the supplied IPv4 binary string into an embedded IPv6 binary
     * string, according to the implemented embedding strategy.
     *
     * @deprecated Implement `CanonicalEmbeddingInterface` and use
     *     `packIntoCanonical()` (identical behaviour) or `packIntoNonCanonical()`
     *     instead. This method will be replaced by the two split packers in the
     *     next major version.
     * @throws \Darsyn\IP\Exception\Strategy\PackingException
     */
    public function pack(string $binary): string;
}
