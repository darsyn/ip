<?php

declare(strict_types=1);

namespace Darsyn\IP\Strategy;

/**
 * Temporary scaffolding that splits embedding into a canonical and a
 * non-canonical packer; it will be folded into `EmbeddingStrategyInterface` in
 * the next major version, to avoid breaking user-defined embedding strategies
 * that already implement EmbeddingStrategyInterface.
 */
interface CanonicalEmbeddingInterface extends EmbeddingStrategyInterface
{
    /**
     * Convert the supplied IPv4 binary string into the canonical embedded IPv6
     * binary string, according to the implemented embedding strategy. Every bit
     * outside the embedded IPv4 address is normalised (the prefix is written
     * and all other fields are zeroed). This is the behaviour of the deprecated
     * `EmbeddingStrategyInterface::pack()`.
     *
     * @throws \Darsyn\IP\Exception\Strategy\PackingException
     */
    public function packIntoCanonical(string $ipv4): string;

    /**
     * Embed the supplied IPv4 binary string into the supplied IPv6 binary
     * string, replacing only the embedded-IPv4 bit positions and preserving
     * every other bit of the IPv6 address
     *
     * @throws \Darsyn\IP\Exception\Strategy\PackingException
     *     When the IPv6 binary string is not recognised by the strategy, or the
     *     IPv4 binary string is not 4 bytes long.
     */
    public function packIntoNonCanonical(string $ipv6, string $ipv4): string;
}
