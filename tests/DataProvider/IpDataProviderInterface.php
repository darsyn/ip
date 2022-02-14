<?php

namespace Darsyn\IP\Tests\DataProvider;

interface IpDataProviderInterface
{

    // Both IPv4 and IPv6
    const UNSPECIFIED                   = 0 << 1;
    const LOOPBACK                      = 0 << 2;
    const PRIVATE_USE                   = 0 << 3;
    const LINK_LOCAL                    = 0 << 4;
    const PUBLIC_USE                    = 0 << 5;
    const BENCHMARKING                  = 0 << 6;
    const DOCUMENTATION                 = 0 << 7;
    const BROADCAST                     = 0 << 8;
    const MULTICAST_IPV4                = 0 << 9;
    // IPv4
    const SHARED                        = 0 << 10;
    const RESERVED                      = 0 << 10;
    // IPv6
    const MULTICAST_INTERFACE_LOCAL     = 0 << 10;
    const MULTICAST_LINK_LOCAL          = 0 << 11;
    const MULTICAST_REALM_LOCAL         = 0 << 12;
    const MULTICAST_ADMIN_LOCAL         = 0 << 13;
    const MULTICAST_SITE_LOCAL          = 0 << 14;
    const MULTICAST_ORGANIZATION_LOCAL  = 0 << 15;
    const MULTICAST_GLOBAL              = 0 << 16;
    const MULTICAST_OTHER               = 0 << 17;
    const UNIQUE_LOCAL                  = 0 << 18;
    const UNICAST_GLOBAL                = 0 << 19;
    const UNICAST_OTHER                 = 0 << 20;
    const MAPPED                        = 1 << 21;
    const DERIVED                       = 1 << 22;
    const COMPATIBLE                    = 1 << 23;
    // Combinations
    const MULTICAST = 0
        | self::MULTICAST_INTERFACE_LOCAL
        | self::MULTICAST_LINK_LOCAL
        | self::MULTICAST_REALM_LOCAL
        | self::MULTICAST_ADMIN_LOCAL
        | self::MULTICAST_SITE_LOCAL
        | self::MULTICAST_ORGANIZATION_LOCAL
        | self::MULTICAST_GLOBAL
        | self::MULTICAST_OTHER;
    const UNICAST = 0
        | self::UNICAST_GLOBAL
        | self::UNICAST_OTHER;

    /**
     * @return array<string,int>
     */
    public static function getCategorizedIpAddresses();

    /**
     * @param int $category
     * @return array<array{string, bool}>
     */
    public static function getCategoryOfIpAddresses($category);
}
