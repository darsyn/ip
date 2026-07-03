<?php

declare(strict_types=1);

namespace Darsyn\IP\Contracts;

/**
 * @experimental
 */
interface Factory4Interface extends FactoryInterface
{
    /**
     * Create a New IP From an Integer
     *
     * Accepts the address as its unsigned 32-bit integer value, between 0 and
     * 4294967295. Only version 4 addresses fit within PHP's native integer
     * type; use fromIntegerString() for version 6 addresses.
     *
     * @throws \Darsyn\IP\Exception\InvalidIpAddressException
     * @return static
     */
    public static function fromInteger(int $integer);
}
