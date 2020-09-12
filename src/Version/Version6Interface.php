<?php declare(strict_types=1);

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
     */
    public function getCompactedAddress(): string;

    /**
     * Get Expanded Address
     *
     * Converts an IP (regardless of version) address into a full IPv6 address
     * (no double colons).
     *
     * @throws \Darsyn\IP\Exception\IpException
     */
    public function getExpandedAddress(): string;
}
