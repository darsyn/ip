<?php

namespace Darsyn\IP;

interface IpInterface
{
    /**
     * Get Binary Representation
     *
     * @return string
     */
    public function getBinary();

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
     * Comparing two IP's of different versions will *always* return false.
     *
     * @param \Darsyn\IP\IpInterface $ip
     * @param int $cidr
     * @throws \Darsyn\IP\Exception\InvalidCidrException
     * @return bool
     */
    public function inRange(IpInterface $ip, $cidr);

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
     * Whether the IP is an IPv4-embedded IPv6 address (either a mapped or
     * compatible address).
     *
     * @return bool
     */
    public function isEmbedded();

    /**
     * Whether the IP is reserved for link-local usage according to
     * RFC 3927/RFC 4291 (IPv4/IPv6).
     *
     * @return bool
     */
    public function isLinkLocal();

    /**
     * Whether the IP is a loopback address according to RFC 2373/RFC 3330
     * (IPv4/IPv6).
     *
     * @return bool
     */
    public function isLoopback();

    /**
     * Whether the IP is a multicast address according to RFC 3171/RFC 2373
     * (IPv4/IPv6).
     *
     * @return bool
     */
    public function isMulticast();

    /**
     * Whether the IP is for private use according to RFC 1918/RFC 4193
     * (IPv4/IPv6).
     *
     * @return bool
     */
    public function isPrivateUse();

    /**
     * Whether the IP is unspecified according to RFC 5735/RFC 2373 (IPv4/IPv6).
     *
     * @return bool
     */
    public function isUnspecified();
}
