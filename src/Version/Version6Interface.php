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
     * @return string
     */
    public function getCompactedAddress();

    /**
     * Get Expanded Address
     *
     * Converts an IP (regardless of version) address into a full IPv6 address
     * (no double colons).
     *
     * @return string
     */
    public function getExpandedAddress();
}
