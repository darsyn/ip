<?php declare(strict_types=1);

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
}
