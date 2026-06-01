<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\DataProvider;

interface IpDataProviderInterface
{
    // Both IPv4 and IPv6
    public const UNSPECIFIED = 1 << 0;
    public const LOOPBACK = 1 << 1;
    public const PRIVATE_USE = 1 << 2;
    public const LINK_LOCAL = 1 << 3;
    public const BENCHMARKING = 1 << 4;
    public const DOCUMENTATION = 1 << 5;
    public const MULTICAST_IPV4 = 1 << 6;

    // IPv4
    public const PUBLIC_USE_V4 = 1 << 7;
    public const BROADCAST = 1 << 8;
    public const SHARED = 1 << 9;
    public const FUTURE_RESERVED = 1 << 10;

    // IPv6
    public const PUBLIC_USE_V6 = 1 << 11;
    public const MULTICAST_INTERFACE_LOCAL = 1 << 12;
    public const MULTICAST_LINK_LOCAL = 1 << 13;
    public const MULTICAST_REALM_LOCAL = 1 << 14;
    public const MULTICAST_ADMIN_LOCAL = 1 << 15;
    public const MULTICAST_SITE_LOCAL = 1 << 16;
    public const MULTICAST_ORGANIZATION_LOCAL = 1 << 17;
    public const MULTICAST_GLOBAL = 1 << 18;
    public const MULTICAST_OTHER = 1 << 19;
    public const UNIQUE_LOCAL = 1 << 20;
    public const UNICAST_GLOBAL = 1 << 21;
    public const UNICAST_OTHER = 1 << 22;
    public const MAPPED = 1 << 23;
    public const DERIVED = 1 << 24;
    public const NAT64 = 1 << 25;
    public const COMPATIBLE = 1 << 26;
    public const LOOPBACK_MAPPED = 1 << 27;
    public const LOOPBACK_COMPATIBLE = 1 << 28;
    public const LOOPBACK_DERIVED = 1 << 29;
    public const LOOPBACK_NAT64 = 1 << 30;

    // Combinations
    public const PUBLIC_USE = 0
        | self::PUBLIC_USE_V4
        | self::PUBLIC_USE_V6;
    public const LOOPBACK_EMBEDDED = 0
        | self::LOOPBACK_MAPPED
        | self::LOOPBACK_COMPATIBLE
        | self::LOOPBACK_DERIVED
        | self::LOOPBACK_NAT64;
    public const MULTICAST = 0
        | self::MULTICAST_IPV4
        | self::MULTICAST_INTERFACE_LOCAL
        | self::MULTICAST_LINK_LOCAL
        | self::MULTICAST_REALM_LOCAL
        | self::MULTICAST_ADMIN_LOCAL
        | self::MULTICAST_SITE_LOCAL
        | self::MULTICAST_ORGANIZATION_LOCAL
        | self::MULTICAST_GLOBAL
        | self::MULTICAST_OTHER;
    public const UNICAST = 0
        | self::LINK_LOCAL
        | self::UNICAST_GLOBAL
        | self::UNICAST_OTHER;

    /** @return array<string, int> */
    public static function getCategorizedIpAddresses();

    /** @return list<array{string, bool}> */
    public static function getCategoryOfIpAddresses(int $category, int $exclude = 0);
}
