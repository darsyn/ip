<?php declare(strict_types=1);

namespace Darsyn\IP\Doctrine;

use Darsyn\IP\Version\IPv6 as IP;

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
     * @return \Darsyn\IP\Version\IPv6
     */
    protected function createIpObject($ip)
    {
        return IP::factory($ip);
    }
}
