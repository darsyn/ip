<?php

namespace Darsyn\IP\Doctrine;

use Darsyn\IP\IPv4 as IP;

/**
 * {@inheritDoc}
 */
class IPv4Type extends AbstractType
{
    const IP_LENGTH = 4;

    /**
     * {@inheritDoc}
     */
    protected function getIpClass()
    {
        return IP::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function createIpObject($ip)
    {
        return new IP($ip);
    }
}
