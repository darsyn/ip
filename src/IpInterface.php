<?php

declare(strict_types=1);

namespace Darsyn\IP;

interface IpInterface
{
    /**
     * @throws \Darsyn\IP\Exception\InvalidIpAddressException
     * @throws \Darsyn\IP\Exception\WrongVersionException
     * @return static
     */
    public static function factory(string $ip);

    /**
     * Get Binary Representation
     */
    public function getBinary(): string;

    /**
     * Do two IP objects represent the same IP address?
     */
    public function equals(self $ip): bool;

    /**
     * Get the IP version from the binary value
     */
    public function getVersion(): int;

    /**
     * Is Version?
     */
    public function isVersion(int $version): bool;

    /**
     * Whether the IP is version 4
     */
    public function isVersion4(): bool;

    /**
     * Whether the IP is version 6
     */
    public function isVersion6(): bool;

    /**
     * Get Network Address
     *
     * Get a new value object from the network address of the original IP.
     *
     * @throws \Darsyn\IP\Exception\InvalidCidrException
     * @return static
     */
    public function getNetworkIp(int $cidr);

    /**
     * Get Broadcast Address
     *
     * Get a new value object from the broadcast address of the original IP.
     *
     * @param int $cidr
     * @throws \Darsyn\IP\Exception\InvalidCidrException
     * @return static
     */
    public function getBroadcastIp(int $cidr);

    /**
     * Is IP Address In Range?
     *
     * Returns a boolean value depending on whether the IP address in question
     * is within the range of the target IP/CIDR combination.
     * Comparing two IPs of different byte-lengths (IPv4 vs IPv6/IPv4-embedded)
     * will throw a WrongVersionException.
     *
     * @throws \Darsyn\IP\Exception\InvalidCidrException
     * @throws \Darsyn\IP\Exception\WrongVersionException
     */
    public function inRange(self $ip, int $cidr): bool;

    /**
     * Get Common CIDR Between IP Addresses
     *
     * Returns the highest common CIDR between the current IP address and another
     *
     * @throws \Darsyn\IP\Exception\WrongVersionException
     */
    public function getCommonCidr(self $ip): int;

    /**
     * Whether the IP is an IPv4-mapped IPv6 address (eg, "::ffff:7f00:1").
     */
    public function isMapped(): bool;

    /**
     * Whether the IP is a 6to4-derived address (eg, "2002:7f00:1::").
     */
    public function isDerived(): bool;

    /**
     * Whether the IP is an IPv4-compatible IPv6 address (eg, `::7f00:1`).
     */
    public function isCompatible(): bool;

    /**
     * Whether the IP is an IPv4-embedded IPv6 address (according to the
     * embedding strategy used).
     */
    public function isEmbedded(): bool;

    /**
     * Whether the IP is reserved for link-local usage, according to
     * RFC 3927/RFC 4291 (IPv4/IPv6).
     */
    public function isLinkLocal(): bool;

    /**
     * Whether the IP is a loopback address, according to RFC 2373/RFC 3330
     * (IPv4/IPv6).
     */
    public function isLoopback(): bool;

    /**
     * Whether the IP is a multicast address, according to RFC 3171/RFC 2373
     * (IPv4/IPv6).
     */
    public function isMulticast(): bool;

    /**
     * Whether the IP is for private use, according to RFC 1918/RFC 4193
     * (IPv4/IPv6).
     */
    public function isPrivateUse(): bool;

    /**
     * Whether the IP is unspecified, according to RFC 5735/RFC 2373 (IPv4/IPv6).
     */
    public function isUnspecified(): bool;

    /**
     * Whether the IP is reserved for network devices benchmarking, according
     * to RFC 2544/RFC 5180 (IPv4/IPv6).
     */
    public function isBenchmarking(): bool;

    /**
     * Whether the IP is in range designated for documentation, according to
     * RFC 5737/RFC 3849 (IPv4/IPv6).
     */
    public function isDocumentation(): bool;

    /**
     * Whether the IP appears to be publicly/globally routable. Please refer to
     * the IANA Special-Purpose Address Registry documents.
     *
     * @see https://www.iana.org/assignments/iana-ipv4-special-registry/iana-ipv4-special-registry.xhtml
     * @see https://www.iana.org/assignments/iana-ipv4-special-registry/iana-ipv6-special-registry.xhtml
     */
    public function isPublicUse(): bool;

    /**
     * Implement string casting for IP objects.
     */
    public function __toString(): string;
}
