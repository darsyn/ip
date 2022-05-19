<?php

namespace Darsyn\IP\Tests\DataProvider;

interface IpDataProviderInterface
{
    // Both IPv4 and IPv6
    const UNSPECIFIED                   = 1 << 0;
    const LOOPBACK                      = 1 << 1;
    const PRIVATE_USE                   = 1 << 2;
    const LINK_LOCAL                    = 1 << 3;
    const BENCHMARKING                  = 1 << 4;
    const DOCUMENTATION                 = 1 << 5;
    const MULTICAST_IPV4                = 1 << 6;
    // IPv4
    const PUBLIC_USE_V4                 = 1 << 7;
    const BROADCAST                     = 1 << 8;
    const SHARED                        = 1 << 9;
    const FUTURE_RESERVED               = 1 << 10;
    // IPv6
    const PUBLIC_USE_V6                 = 1 << 11;
    const MULTICAST_INTERFACE_LOCAL     = 1 << 12;
    const MULTICAST_LINK_LOCAL          = 1 << 13;
    const MULTICAST_REALM_LOCAL         = 1 << 14;
    const MULTICAST_ADMIN_LOCAL         = 1 << 15;
    const MULTICAST_SITE_LOCAL          = 1 << 16;
    const MULTICAST_ORGANIZATION_LOCAL  = 1 << 17;
    const MULTICAST_GLOBAL              = 1 << 18;
    const MULTICAST_OTHER               = 1 << 19;
    const UNIQUE_LOCAL                  = 1 << 20;
    const UNICAST_GLOBAL                = 1 << 21;
    const UNICAST_OTHER                 = 1 << 22;
    const MAPPED                        = 1 << 23;
    const DERIVED                       = 1 << 24;
    const COMPATIBLE                    = 1 << 25;
    const LOOPBACK_MAPPED               = 1 << 26;
    const LOOPBACK_COMPATIBLE           = 1 << 27;
    const LOOPBACK_DERIVED              = 1 << 28;
    // Combinations
    const PUBLIC_USE = 0
        | self::PUBLIC_USE_V4
        | self::PUBLIC_USE_V6;
    const LOOPBACK_EMBEDDED = 0
        | self::LOOPBACK_MAPPED
        | self::LOOPBACK_COMPATIBLE
        | self::LOOPBACK_DERIVED;
    const MULTICAST = 0
        | self::MULTICAST_IPV4
        | self::MULTICAST_INTERFACE_LOCAL
        | self::MULTICAST_LINK_LOCAL
        | self::MULTICAST_REALM_LOCAL
        | self::MULTICAST_ADMIN_LOCAL
        | self::MULTICAST_SITE_LOCAL
        | self::MULTICAST_ORGANIZATION_LOCAL
        | self::MULTICAST_GLOBAL
        | self::MULTICAST_OTHER;
    const UNICAST = 0
        | self::LINK_LOCAL
        | self::UNICAST_GLOBAL
        | self::UNICAST_OTHER;

    /**
     * @return array<string,int>
     */
    public static function getCategorizedIpAddresses();

    /**
     * @param int $category
     * @param int $exclude
     * @return array<array{string, bool}>
     */
    public static function getCategoryOfIpAddresses($category, $exclude = 0);
}
