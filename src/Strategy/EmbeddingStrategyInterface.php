<?php

namespace Darsyn\IP\Strategy;

interface EmbeddingStrategyInterface
{
    /**
     * Checks if the IPv6 binary string supplied contains an IPv4 embedded
     * according to the implemented embedding strategy.
     *
     * @param string $binary
     * @return bool
     */
    public function isEmbedded($binary);

    /**
     * Extract the embedded IPv4 binary string from the IPv6 binary string
     * supplied, according to the implemented embedding strategy.
     *
     * @param string $binary
     * @throws \Darsyn\IP\Exception\Strategy\ExtractionException
     * @return string
     */
    public function extract($binary);

    /**
     * Convert the supplied IPv4 binary string into an embedded IPv6 binary
     * string, according to the implemented embedding strategy.
     *
     * @param string $binary
     * @throws \Darsyn\IP\Exception\Strategy\PackingException
     * @return string
     */
    public function pack($binary);
}
