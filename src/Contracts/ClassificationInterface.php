<?php

declare(strict_types=1);

namespace Darsyn\IP\Contracts;

/**
 * @experimental
 */
interface ClassificationInterface
{
    /**
     * Whether the IP is reserved for link-local usage, according to RFC 3927
     * (IPv4) or RFC 4291 § 2.5.6 (IPv6).
     */
    public function isLinkLocal(): bool;

    /**
     * Whether the IP is a loopback address, according to RFC 1122 § 3.2.1.3
     * (IPv4) or RFC 4291 § 2.5.3 (IPv6).
     */
    public function isLoopback(): bool;

    /**
     * Whether the IP is a multicast address, according to RFC 5771 (IPv4) or
     * RFC 4291 § 2.7 (IPv6).
     */
    public function isMulticast(): bool;

    /**
     * Whether the IP is for private use, according to RFC 1918/RFC 4193
     * (IPv4/IPv6).
     */
    public function isPrivateUse(): bool;

    /**
     * Whether the IP is unspecified ("this host on this network"), according
     * to RFC 1122 § 3.2.1.3 (IPv4) or RFC 4291 § 2.5.2 (IPv6).
     */
    public function isUnspecified(): bool;

    /**
     * Whether the IP is reserved for network devices benchmarking, according
     * to RFC 2544 (IPv4) or RFC 5180 (IPv6). The IPv6 block printed in RFC 5180
     * itself is wrong; RFC Errata 1752 corrects it to `2001:2::/48`.
     */
    public function isBenchmarking(): bool;

    /**
     * Whether the IP is in a range designated for documentation, according to
     * RFC 5737 and RFC 5771 § 9.2 (IPv4), or RFC 3849 and RFC 9637 (IPv6).
     */
    public function isDocumentation(): bool;

    /**
     * Whether the IP appears to be publicly/globally routable. Please refer to
     * the IANA Special-Purpose Address Registry documents.
     *
     * @see https://www.iana.org/assignments/iana-ipv4-special-registry/iana-ipv4-special-registry.xhtml
     * @see https://www.iana.org/assignments/iana-ipv6-special-registry/iana-ipv6-special-registry.xhtml
     */
    public function isGloballyReachable(): bool;
}
