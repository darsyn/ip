# Type Methods

The type methods return a boolean value depending on whether the IP address is a
certain type.

## Detecting Embedding Strategies

### Embedded?

Whether the IP is an IPv4 address embedded into an IPv6 address, according to
the embedding strategy used when creating the IP object.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('::ffff:7f00:1');
$ip->isEmbedded(); // bool(true)
```

If you would like to detect if the IP is an IPv4-embedded IPv6 address,
according to [RFC 4291 section 2.5.5](https://tools.ietf.org/html/rfc4291.html
"IP Version 6 Addressing Architecture"), please use the following conditional
statement:

```php
<?php
use Darsyn\IP\Strategy\Derived;
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('127.0.0.1', new Derived);
$rfc4291 = $ip->isMapped() || $ip->isCompatible(); // bool(false)
```

### Mapped

Whether the IP is an IPv4-mapped IPv6 address (eg, `::ffff:7f00:1`), according
to [RFC 4291 section 2.5.5.2](https://tools.ietf.org/html/rfc4291 "IP Version 6
Addressing Architecture"). The `IPv4` class will always return `bool(false)` for
this method.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('::ffff:7f00:1');
$ip->isMapped(); // bool(true)
```

### Derived

Whether the IP is a 6to4-derived IPv6 address (eg, `2002:7f00:1::`), according
to [RFC 3056](https://tools.ietf.org/html/rfc3056 "Connection of IPv6 Domains
via IPv4 Clouds"). The `IPv4` class will always return `bool(false)` for this
method.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('2002:7f00:1::');
$ip->isDerived(); // bool(true)
```

### Compatible

Whether the IP is an IPv4-compatible IPv6 address (eg, `::7f00:1`), according to
[RFC 4291 section 2.5.5.1](https://tools.ietf.org/html/rfc4291.html "IP Version
6 Addressing Architecture"). The `IPv4` class will always return `bool(false)`
for this method.

> IPv4-compatible IPv6 addresses are deprecated in the RFC.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('::7f00:1');
$ip->isCompatible(); // bool(true)
```

### NAT64

Whether the IP is a NAT64 address within the Well-Known Prefix `64:ff9b::/96`,
according to [RFC 6052 section 2.1](https://tools.ietf.org/html/rfc6052 "IPv6
Addressing of IPv4/IPv6 Translators"), or within the Local-use Prefix
`64:ff9b:1::/48`, according to [RFC 8215 section 4](https://tools.ietf.org/html/rfc8215
"Local-Use IPv4/IPv6 Translation Prefix").

```php
<?php
use Darsyn\IP\Version\IPv6 as IP;

IP::fromProtocol('64:ff9b::7f00:1')->isNat64WellKnown(); // bool(true)
IP::fromProtocol('64:ff9b:1:7f00:0:1::')->isNat64LocalUse(); // bool(true)
```

### Teredo

Whether the IP is a Teredo tunnelling address within `2001::/32`, according to
[RFC 4380 section 4](https://tools.ietf.org/html/rfc4380 "Teredo: Tunneling
IPv6 over UDP through NATs").

```php
<?php
use Darsyn\IP\Version\IPv6 as IP;

$ip = IP::fromProtocol('2001::8500:0:0:80ff:fffe');
$ip->isTeredo(); // bool(true)
```

### According to Any Strategy

`isEmbeddedAccordingToStrategy()` is the generic form of the named detection
predicates: any [embedding strategy](./05-strategies.md), including a
user-defined one, can be tested against the address without constructing a new
IP object around it.

```php
<?php
use Darsyn\IP\Strategy\Derived;
use Darsyn\IP\Version\IPv6 as IP;

$ip = IP::fromProtocol('2002:7f00:1::');
$ip->isEmbeddedAccordingToStrategy(new Derived()); // bool(true)
```

### Embedded IP

`getEmbeddedIp()` extracts the IPv4 address embedded in this address as an
`IPv4` instance, and the inverse of `IPv6::fromEmbedded()`. A
`WrongVersionException` is thrown when no IPv4 address is embedded according to
the strategy in effect. A null strategy falls back to the instance's own
embedding strategy on `Multi`, or to the global default set via
`Multi::setDefaultEmbeddingStrategy()`

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::fromProtocol('::ffff:7f00:1');
$ip->getEmbeddedIp()->getDotAddress(); // string("127.0.0.1")
```

## Detecting Address Types

### Link Local

Whether the IP is reserved for link-local usage, according to [RFC
3927](https://tools.ietf.org/html/rfc3927 "Dynamic Configuration of IPv4
Link-Local Addresses") (IPv4) or [RFC 4291 section
2.4](https://tools.ietf.org/html/rfc4291 "IP Version 6 Addressing Architecture")
(IPv6).

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('127.0.0.1');
$ip->isLinkLocal(); // bool(false)
```

### Loopback

Whether the IP is a loopback address, according to [RFC 1122 section
3.2.1.3](https://tools.ietf.org/html/rfc1122 "Requirements for Internet Hosts --
Communication Layers") (IPv4) or [RFC 4291 section
2.5.3](https://tools.ietf.org/html/rfc2373 "IP Version 6 Addressing
Architecture") (IPv6).

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('127.0.0.1');
$ip->isLoopback(); // bool(true)
```

### Multicast

Whether the IP is a multicast address, according to [RFC
5771](https://tools.ietf.org/html/rfc5771 "IANA Guidelines for IPv4 Multicast
Address Assignments") (IPv4) or [RFC 4291 section
2.7](https://tools.ietf.org/html/rfc4291 "IP Version 6 Addressing Architecture")
(IPv6).

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('127.0.0.1');
$ip->isMulticast(); // bool(false)
```

### Private Use

Whether the IP is for private use, according to
[RFC 1918 section 3](https://tools.ietf.org/html/rfc1918 "Address Allocation for
Private Internets") (IPv4) or [RFC 4193](https://tools.ietf.org/html/rfc4193
"Unique Local IPv6 Unicast Addresses") (IPv6).

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('127.0.0.1');
$ip->isPrivateUse(); // bool(false)
```

### Unspecified

Whether the IP is unspecified, according to
[RFC 5735](https://tools.ietf.org/html/rfc5735 "Special Use IPv4 Addresses")
(IPv4) or [RFC 2373 section 2.5.2](https://tools.ietf.org/html/rfc2373 "IP
Version 6 Addressing Architecture") (IPv6).

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('127.0.0.1');
$ip->isUnspecified(); // bool(false)
```

### Benchmarking

Whether the IP is reserved for network devices benchmarking, according to
[RFC 2544](https://tools.ietf.org/html/rfc2544 "Benchmarking Methodology for
Network Interconnect Devices") corrected in [errata
423](https://www.rfc-editor.org/errata/eid423) (IPv4) or [RFC
5180](https://tools.ietf.org/html/rfc5180 "IPv6 Benchmarking Methodology for
Network Interconnect Devices") corrected in [errata
1752](https://www.rfc-editor.org/errata/eid1752) (IPv6).

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('127.0.0.1');
$ip->isBenchmarking(); // bool(false)
```

### Documentation

Whether the IP is in range designated for documentation, according to
[RFC 5737](https://tools.ietf.org/html/rfc5737 "IPv4 Address Blocks Reserved for
Documentation") (IPv4) or [RFC 3849](https://tools.ietf.org/html/rfc3849 "IPv6
Address Prefix Reserved for Documentation") (IPv6).

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('127.0.0.1');
$ip->isDocumentation(); // bool(false)
```

### Globally Reachable

Whether the IP appears to be publicly/globally routable (please refer to the
following:

- [IANA IPv4 Special-Purpose Address Registry](https://www.iana.org/assignments/iana-ipv4-special-registry/iana-ipv4-special-registry.xhtml)
- [IANA IPv6 Special-Purpose Address Registry](https://www.iana.org/assignments/iana-ipv6-special-registry/iana-ipv6-special-registry.xhtml)

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('127.0.0.1');
$ip->isGloballyReachable(); // bool(false)
```

## IPv4 Specific

These methods will throw a `WrongVersionException` if called from `Multi` on an
IPv6 address (non IPv4-embedded).

### Broadcast

Whether the IP is a broadcast address, according to
[RFC 919](https://tools.ietf.org/html/rfc919 "BROADCASTING INTERNET DATAGRAMS").

```php
<?php
use Darsyn\IP\Version\IPv4;

IPv4::factory('127.0.0.1')->isBroadcast(); // bool(false)
IPv4::factory('255.255.255.255')->isBroadcast(); // bool(true)
```

### Reserved for Future Use

Whether the IP is reserved for future use, according to [RFC
1112](https://tools.ietf.org/html/rfc1112 "Host Extensions for IP Multicasting").

```php
<?php
use Darsyn\IP\Version\IPv4;

IPv4::factory('127.0.0.1')->isFutureReserved(); // bool(false)
IPv4::factory('255.34.85.169')->isFutureReserved(); // bool(true)
```

### Shared

Whether the IP is part of the Shared Address Space, according to [RFC
6598](https://tools.ietf.org/html/rfc6598 "IANA-Reserved IPv4 Prefix for Shared
Address Space").

```php
<?php
use Darsyn\IP\Version\IPv4;

IPv4::factory('100.128.179.30')->isShared(); // bool(false)
IPv4::factory('100.127.43.2')->isShared(); // bool(true)
```

## IPv6 Specific

### Multicast Scope

The specific scope of the multicast address (returns `null` if not a multicast address).
The following constants are available on `Darsyn\IP\Version\Version6Interface`:

- `MULTICAST_INTERFACE_LOCAL`
- `MULTICAST_LINK_LOCAL`
- `MULTICAST_REALM_LOCAL`
- `MULTICAST_ADMIN_LOCAL`
- `MULTICAST_SITE_LOCAL`
- `MULTICAST_ORGANIZATION_LOCAL`
- `MULTICAST_GLOBAL`

```php
<?php
use Darsyn\IP\Version\IPv6;

$isOrganizationLocal = IPv6::factory('ff08:1:6e6f:cbb::980e:3816')->getMulticastScope() === IPv6::MULTICAST_ORGANIZATION_LOCAL; // bool(true)
```

### Unique Local

Whether the IP is a unique local address, according to [RFC
4193](https://tools.ietf.org/html/rfc4193 "Unique Local IPv6 Unicast Addresses").

```php
<?php
use Darsyn\IP\Version\IPv6;

IPv6::factory('b638:cc70:716:c4d4:f69c:4ee3:6c65:a0b2')->isUniqueLocal(); // bool(false)
IPv6::factory('fdff:ffff::')->isUniqueLocal(); // bool(true)
```

### Unicast

Whether the IP is a unicast address, according to [RFC
4291](https://tools.ietf.org/html/rfc4291 "IP Version 6 Addressing
Architecture") (any IPv6 address that is not a multicast address is unicast, and
vice-versa).

```php
<?php
use Darsyn\IP\Version\IPv6;

IPv6::factory('ff08::')->isUnicast(); // bool(false)
IPv6::factory('::ffff:1:0')->isUnicast(); // bool(true)
```

### Unicast Global

Whether the IP is a globally routable unicast address, according to [RFC 4291
section 2.5.4](https://tools.ietf.org/html/rfc2941 "IP Version 6 Addressing
Architecture").

```php
<?php
use Darsyn\IP\Version\IPv6;

IPv6::factory('2001:db8:85a3::8a2e:370:7334')->isUnicastGlobal(); // bool(false)
IPv6::factory('140c:12f1:6e6f:c0bb:980e:3816:3e52:1193')->isUnicastGlobal(); // bool(true)
```
