<?php

declare(strict_types=1);

namespace Darsyn\IP\Contracts;

use Darsyn\IP\Formatter\ProtocolFormatterInterface;

/**
 * @experimental
 */
interface Output4Interface extends OutputInterface
{
    /**
     * Convert to Dot Address Notation
     *
     * Convert an IP into an IPv4 dot-notation address string
     * This method will NOT work with IPv6 addresses.
     *
     * @throws \Darsyn\IP\Exception\IpException
     * @throws \Darsyn\IP\Exception\WrongVersionException
     */
    public function toDotAddress(?ProtocolFormatterInterface $formatter = null): string;

    /**
     * Convert to Integer
     *
     * Convert an IP into its unsigned 32-bit integer value, between 0 and
     * 4294967295. This method will NOT work with IPv6 addresses.
     *
     * @throws \Darsyn\IP\Exception\WrongVersionException
     * @return int<0, 4294967295>
     */
    public function toInteger(): int;
}
