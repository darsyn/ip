<?php

namespace Darsyn\IP;

interface IpInterface
{
    /**
     * @param string $ip
     * @throws \Darsyn\IP\Exception\InvalidIpAddressException
     * @throws \Darsyn\IP\Exception\WrongVersionException
     * @return static
     */
    public static function factory($ip);

    /**
     * Get Binary Representation
     *
     * @return string
     */
    public function getBinary();

    /**
     * Do two IP objects represent the same IP address?
     *
     * @param \Darsyn\IP\IpInterface $ip
     * @return bool
     */
    public function equals(IpInterface $ip);

    /**
     * Get the IP version from the binary value
     *
     * @return int
     */
    public function getVersion();

    /**
     * Is Version?
     *
     * @param int $version
     * @return bool
     */
    public function isVersion($version);

    /**
     * Whether the IP is version 4
     *
     * @return bool
     */
    public function isVersion4();

    /**
     * Whether the IP is version 6
     *
     * @return bool
     */
    public function isVersion6();

    /**
     * Get Network Address
     *
     * Get a new value object from the network address of the original IP.
     *
     * @param int $cidr
     * @throws \Darsyn\IP\Exception\InvalidCidrException
     * @return static
     */
    public function getNetworkIp($cidr);

    /**
     * Get Broadcast Address
     *
     * Get a new value object from the broadcast address of the original IP.
     *
     * @param int $cidr
     * @throws \Darsyn\IP\Exception\InvalidCidrException
     * @return static
     */
    public function getBroadcastIp($cidr);

    /**
     * Is IP Address In Range?
     *
     * Returns a boolean value depending on whether the IP address in question
     * is within the range of the target IP/CIDR combination.
     * Comparing two IPs of different byte-lengths (IPv4 vs IPv6/IPv4-embedded)
     * will throw a WrongVersionException.
     *
     * @param \Darsyn\IP\IpInterface $ip
     * @param int $cidr
     * @throws \Darsyn\IP\Exception\InvalidCidrException
     * @throws \Darsyn\IP\Exception\WrongVersionException
     * @return bool
     */
    public function inRange(IpInterface $ip, $cidr);

    /**
     * Get Common CIDR Between IP Addresses
     *
     * Returns the highest common CIDR between the current IP address and another
     *
     * @param \Darsyn\IP\IpInterface $ip
     * @throws \Darsyn\IP\Exception\WrongVersionException
     * @return int
     */
    public function getCommonCidr(IpInterface $ip);

    /**
     * Whether the IP is an IPv4-mapped IPv6 address (eg, "::ffff:7f00:1").
     *
     * @return bool
     */
    public function isMapped();

    /**
     * Whether the IP is a 6to4-derived address (eg, "2002:7f00:1::").
     *
     * @return bool
     */
    public function isDerived();

    /**
     * Whether the IP is an IPv4-compatible IPv6 address (eg, `::7f00:1`).
     *
     * @return bool
     */
    public function isCompatible();

    /**
     * Whether the IP is an IPv4-embedded IPv6 address (according to the
     * embedding strategy used).
     *
     * @return bool
     */
    public function isEmbedded();

    /**
     * Whether the IP is reserved for link-local usage, according to
     * RFC 3927/RFC 4291 (IPv4/IPv6).
     *
     * @return bool
     */
    public function isLinkLocal();

    /**
     * Whether the IP is a loopback address, according to RFC 2373/RFC 3330
     * (IPv4/IPv6).
     *
     * @return bool
     */
    public function isLoopback();

    /**
     * Whether the IP is a multicast address, according to RFC 3171/RFC 2373
     * (IPv4/IPv6).
     *
     * @return bool
     */
    public function isMulticast();

    /**
     * Whether the IP is for private use, according to RFC 1918/RFC 4193
     * (IPv4/IPv6).
     *
     * @return bool
     */
    public function isPrivateUse();

    /**
     * Whether the IP is unspecified, according to RFC 5735/RFC 2373 (IPv4/IPv6).
     *
     * @return bool
     */
    public function isUnspecified();

    /**
     * Whether the IP is reserved for network devices benchmarking, according
     * to RFC 2544/RFC 5180 (IPv4/IPv6).
     *
     * @return bool
     */
    public function isBenchmarking();

    /**
     * Whether the IP is in range designated for documentation, according to
     * RFC 5737/RFC 3849 (IPv4/IPv6).
     *
     * @return bool
     */
    public function isDocumentation();

    /**
     * Whether the IP appears to be publicly/globally routable. Please refer to
     * the IANA Special-Purpose Address Registry documents.
     *
     * @see https://www.iana.org/assignments/iana-ipv4-special-registry/iana-ipv4-special-registry.xhtml
     * @see https://www.iana.org/assignments/iana-ipv4-special-registry/iana-ipv6-special-registry.xhtml
     *
     * @return bool
     */
    public function isPublicUse();

    /**
     * Implement string casting for IP objects.
     *
     * @return string
     */
    public function __toString();
}
