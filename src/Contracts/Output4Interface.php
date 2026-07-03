<?php

declare(strict_types=1);

namespace Darsyn\IP\Contracts;

/**
 * @experimental
 */
interface Output4Interface extends OutputInterface
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
     * Get Integer
     *
     * Convert an IP into its unsigned 32-bit integer value, between 0 and
     * 4294967295. This method will NOT work with IPv6 addresses.
     *
     * @throws \Darsyn\IP\Exception\WrongVersionException
     * @return int<0, 4294967295>
     */
    public function toInteger(): int;
}
