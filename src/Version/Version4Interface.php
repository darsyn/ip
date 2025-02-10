<?php

declare(strict_types=1);

namespace Darsyn\IP\Version;

use Darsyn\IP\IpInterface;

interface Version4Interface extends IpInterface
{
    /**
     * Get Dot Address
     *
     * Convert an IP into an IPv4 dot-notation address string
     * This method will NOT work with IPv6 addresses.
     *
     * @throws \Darsyn\IP\Exception\IpException
     * @throws \Darsyn\IP\Exception\WrongVersionException
     */
    public function getDotAddress(): string;

    /**
     * Whether the IP is a broadcast address, according to RFC 919.
     *
     * @throws \Darsyn\IP\Exception\WrongVersionException
     */
    public function isBroadcast(): bool;

    /**
     * Whether the IP is part of the Shared Address Space, according to RFC 6598.
     *
     * @throws \Darsyn\IP\Exception\WrongVersionException
     */
    public function isShared(): bool;

    /**
     * Whether the IP is reserved for future use, according to RFC 1112.
     *
     * @throws \Darsyn\IP\Exception\WrongVersionException
     */
    public function isFutureReserved(): bool;
}
