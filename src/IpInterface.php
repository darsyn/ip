<?php declare(strict_types=1);

namespace Darsyn\IP;

interface IpInterface
{
    /**
     * @throws \Darsyn\IP\Exception\InvalidIpAddressException
     * @throws \Darsyn\IP\Exception\WrongVersionException
     * @return \Darsyn\IP\IpInterface
     */
    public static function factory(string $ip);

    /** Get Binary Representation */
    public function getBinary(): string;

    /** Get the IP version from the binary value */
    public function getVersion(): int;

    public function isVersion(int $version): bool;

    /** Whether the IP is version 4 */
    public function isVersion4(): bool;

    /** Whether the IP is version 6 */
    public function isVersion6(): bool;

    /**
     * Get a new value object from the network address of the original IP.
     *
     * @throws \Darsyn\IP\Exception\InvalidCidrException
     */
    public function getNetworkIp(int $cidr): self;

    /**
     * Get a new value object from the broadcast address of the original IP.
     *
     * @throws \Darsyn\IP\Exception\InvalidCidrException
     */
    public function getBroadcastIp(int $cidr): self;

    /**
     * Returns a boolean value depending on whether the IP address in question
     * is within the range of the target IP/CIDR combination.
     * Comparing two IP's of different versions will *always* return false.
     *
     * @throws \Darsyn\IP\Exception\InvalidCidrException
     */
    public function inRange(IpInterface $ip, int $cidr): bool;

    /** Whether the IP is an IPv4-mapped IPv6 address (eg, "::ffff:7f00:1") */
    public function isMapped(): bool;

    /** Whether the IP is a 6to4-derived address (eg, "2002:7f00:1::") */
    public function isDerived(): bool;

    /** Whether the IP is an IPv4-compatible IPv6 address (eg, `::7f00:1`) */
    public function isCompatible(): bool;

    /**
     * Whether the IP is an IPv4-embedded IPv6 address (either a mapped or
     * compatible address).
     */
    public function isEmbedded(): bool;

    /**
     * Whether the IP is reserved for link-local usage according to
     * RFC 3927/RFC 4291 (IPv4/IPv6).
     */
    public function isLinkLocal(): bool;

    /**
     * Whether the IP is a loopback address according to RFC 2373/RFC 3330
     * (IPv4/IPv6).
     */
    public function isLoopback(): bool;

    /**
     * Whether the IP is a multicast address according to RFC 3171/RFC 2373
     * (IPv4/IPv6).
     */
    public function isMulticast(): bool;

    /**
     * Whether the IP is for private use according to RFC 1918/RFC 4193
     * (IPv4/IPv6).
     */
    public function isPrivateUse(): bool;

    /**
     * Whether the IP is unspecified according to RFC 5735/RFC 2373 (IPv4/IPv6).
     */
    public function isUnspecified(): bool;
}
