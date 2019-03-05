<?php declare(strict_types=1);

namespace Darsyn\IP\Doctrine;

use Darsyn\IP\IpInterface;
use Darsyn\IP\Version\IPv6 as IP;

/**
 * {@inheritDoc}
 */
class IPv6Type extends AbstractType
{
    protected function getIpClass(): string
    {
        return IP::class;
    }

    protected function createIpObject(string $ip): IpInterface
    {
        return IP::factory($ip);
    }
}
