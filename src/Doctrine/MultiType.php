<?php

namespace Darsyn\IP\Doctrine;

use Darsyn\IP\Version\Multi as IP;

/**
 * {@inheritDoc}
 */
class MultiType extends AbstractType
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
        return IP::factory($ip);
    }
}
