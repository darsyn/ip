<?php

namespace Darsyn\IP\Version;

use Darsyn\IP\IpInterface;

interface Version6Interface extends IpInterface
{
    const MULTICAST_INTERFACE_LOCAL = 1;
    const MULTICAST_LINK_LOCAL = 2;
    const MULTICAST_REALM_LOCAL = 3;
    const MULTICAST_ADMIN_LOCAL = 4;
    const MULTICAST_SITE_LOCAL = 5;
    const MULTICAST_ORGANIZATION_LOCAL = 8;
    const MULTICAST_GLOBAL = 14;

    /**
     * Get Compacted Address
     *
     * Converts an IP (regardless of version) into a compacted IPv6 address
     * (including double-colons if appropriate).
     *
     * @throws \Darsyn\IP\Exception\IpException
     * @return string
     */
    public function getCompactedAddress();

    /**
     * Get Expanded Address
     *
     * Converts an IP (regardless of version) address into a full IPv6 address
     * (no double colons).
     *
     * @throws \Darsyn\IP\Exception\IpException
     * @return string
     */
    public function getExpandedAddress();

    /**
     * Returns the IP address’s multicast scope if the address is multicast,
     * null otherwise. Return values are integers mapped to the MULTICAST_*
     * constants on this interface.
     *
     * @return int|null
     */
    public function getMulticastScope();

    /**
     * Whether the IP is a unique local address, according to RFC 4193.
     *
     * @return bool
     */
    public function isUniqueLocal();

    /**
     * Whether the IP is a unicast address, according to RFC 4291.
     *
     * @return bool
     */
    public function isUnicast();

    /**
     * Whether the IP is a globally routable unicast address, according to
     * RFC 2941.
     *
     * @return bool
     */
    public function isUnicastGlobal();
}
