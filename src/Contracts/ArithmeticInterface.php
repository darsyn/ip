<?php

declare(strict_types=1);

namespace Darsyn\IP\Contracts;

/**
 * @experimental
 */
interface ArithmeticInterface
{
    /**
     * Get Network Address
     *
     * Get a new value object from the network address of the original IP.
     *
     * @throws \Darsyn\IP\Exception\InvalidCidrException
     * @return static
     */
    public function getNetworkIp(int $cidr);

    /**
     * Get Broadcast Address
     *
     * Get a new value object from the broadcast address of the original IP.
     *
     * @throws \Darsyn\IP\Exception\InvalidCidrException
     * @return static
     */
    public function getBroadcastIp(int $cidr);

    /**
     * Get the Next Address
     *
     * @throws \Darsyn\IP\Exception\OverflowException
     * @return static
     */
    public function next();

    /**
     * Get the Previous Address
     *
     * @throws \Darsyn\IP\Exception\OverflowException
     * @return static
     */
    public function previous();

    /**
     * Get an Offset Address
     *
     * @throws \Darsyn\IP\Exception\OverflowException
     * @return static
     */
    public function offset(int $offset);
}
