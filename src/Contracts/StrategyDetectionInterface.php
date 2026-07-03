<?php

declare(strict_types=1);

namespace Darsyn\IP\Contracts;

use Darsyn\IP\Strategy\EmbeddingStrategyInterface;
use Darsyn\IP\Version\IPv4;

/**
 * @experimental
 */
interface StrategyDetectionInterface
{
    /**
     * Whether an IPv4 address is embedded within this address, according to
     * the supplied embedding strategy.
     *
     * This is the generic form of the named detection predicates; any strategy
     * implementation (including a user-defined one) can be tested against the
     * address without constructing a new IP object around it.
     */
    public function isEmbeddedAccordingToStrategy(EmbeddingStrategyInterface $strategy): bool;

    /**
     * Whether the IP is an IPv4-mapped IPv6 address, according to
     * RFC 4291 § 2.5.5.2 (eg, "::ffff:7f00:1").
     */
    public function isMapped(): bool;

    /**
     * Whether the IP is a 6to4-derived address, according to RFC 3056 § 2 (eg,
     * "2002:7f00:1::").
     */
    public function isDerived(): bool;

    /**
     * Whether the IP is an IPv4-compatible IPv6 address, according to
     * RFC 4291 § 2.5.5.1 (eg, `::7f00:1`); deprecated by that same RFC.
     */
    public function isCompatible(): bool;

    /**
     * Whether the IP is a NAT64 address within the Well-Known Prefix
     * `64:ff9b::/96`, according to RFC 6052 § 2.1 (eg, "64:ff9b::7f00:1").
     */
    public function isNat64WellKnown(): bool;

    /**
     * Whether the IP is a NAT64 address within the Local-use Prefix
     * `64:ff9b:1::/48`, according to RFC 8215 § 4.
     */
    public function isNat64LocalUse(): bool;

    /**
     * Whether the IP is a Teredo tunnelling address within `2001::/32`,
     * according to RFC 4380 § 4.
     */
    public function isTeredo(): bool;

    /**
     * Get the IPv4 address embedded in this address as an IPv4 instance.
     *
     * A null strategy falls back to the instance's own embedding strategy on
     * Multi, or to the global default set via
     * Multi::setDefaultEmbeddingStrategy() otherwise.
     *
     * @throws \Darsyn\IP\Exception\WrongVersionException when no IPv4 address
     *         is embedded according to the strategy in effect.
     */
    public function getEmbeddedIp(?EmbeddingStrategyInterface $strategy = null): IPv4;
}
