<?php declare(strict_types=1);

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
     * @return \Darsyn\IP\Version\Multi
     */
    protected function createIpObject($ip)
    {
        return IP::factory($ip);
    }
}
