<?php

namespace Darsyn\IP\Version;

use Darsyn\IP\IpInterface;

interface Version6Interface extends IpInterface
{
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
     * RFC 2941 (section 2.5.7).
     *
     * @return bool
     */
    public function isUnicastGlobal();
}
