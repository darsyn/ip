<?php

namespace Darsyn\IP\Doctrine;

use Darsyn\IP\IPv6 as IP;

/**
 * {@inheritDoc}
 */
class IPv6Type extends AbstractType
{
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
