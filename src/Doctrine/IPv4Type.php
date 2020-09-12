<?php declare(strict_types=1);

namespace Darsyn\IP\Doctrine;

use Darsyn\IP\Version\IPv4 as IP;

class IPv4Type extends AbstractType
{
    const IP_LENGTH = 4;

    protected function getIpClass()
    {
        return IP::class;
    }

    /**
     * @inheritDoc
     * @return \Darsyn\IP\Version\IPv4
     */
    protected function createIpObject($ip)
    {
        return IP::factory($ip);
    }
}
