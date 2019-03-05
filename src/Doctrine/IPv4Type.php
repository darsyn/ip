<?php declare(strict_types=1);

namespace Darsyn\IP\Doctrine;

use Darsyn\IP\IpInterface;
use Darsyn\IP\Version\IPv4 as IP;

/**
 * {@inheritDoc}
 */
class IPv4Type extends AbstractType
{
    protected const IP_LENGTH = 4;

    protected function getIpClass(): string
    {
        return IP::class;
    }

    protected function createIpObject(string $ip): IpInterface
    {
        return IP::factory($ip);
    }
}
