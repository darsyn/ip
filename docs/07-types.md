## Type Methods

The type methods return a boolean value depending on whether the IP address is a
certain type.

### Mapped

Whether the IP is an IPv4-mapped IPv6 address (eg, `::ffff:7f00:1`) according to
[RFC 4291](https://tools.ietf.org/html/rfc4291#section-2.5.5.2
"IP Version 6 Addressing Architecture"). The `IPV4` class will always return
`bool(false)` for this method.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = new IP('::ffff:7f00:1');
$ip->isMapped(); // bool(true)
```

### Derived

Whether the IP is a 6to4-derived IPv6 address (eg, `2002:7f00:1::`) according
to [RFC 3056](https://tools.ietf.org/html/rfc3056
"Connection of IPv6 Domains via IPv4 Clouds"). The `IPV4` class will always return
`bool(false)` for this method.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = new IP('2002:7f00:1::');
$ip->isDerived(); // bool(true)
```

### Compatible

Whether the IP is an IPv4-compatible IPv6 address (eg, `::7f00:1`) according to
[RFC 4291](https://tools.ietf.org/html/rfc4291.html#section-2.5.5.1
"IP Version 6 Addressing Architecture"). The `IPV4` class will always return
`bool(false)` for this method.

> IPv4-compatible IPv6 addresses are deprecated in the RFC.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = new IP('::7f00:1');
$ip->isCompatible(); // bool(true)
```

### Embedded

Whether the IP is an IPv4-embedded IPv6 address (either a mapped or compatible
address) according to
[RFC 4291](https://tools.ietf.org/html/rfc4291.html#section-2.5.5
"IP Version 6 Addressing Architecture"). The `IPV4` class will always return
`bool(false)` for this method.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = new IP('::ffff:7f00:1');
$ip->isEmbedded(); // bool(true)
```

### Link Local

Whether the IP is reserved for link-local usage according to
[RFC 3927](https://tools.ietf.org/html/rfc3927 "Dynamic Configuration of IPv4
Link-Local Addresses") (IPv4) or [RFC 4291](https://tools.ietf.org/html/rfc4291
"IP Version 6 Addressing Architecture") (IPv6).

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = new IP('127.0.0.1');
$ip->isLinkLocal(); // bool(false)
```

### Loopback

Whether the IP is a loopback address according to
[RFC 3330](https://tools.ietf.org/html/rfc3330 "Special-Use IPv4 Addresses")
(IPv4) or [RFC 2373](https://tools.ietf.org/html/rfc2373
"IP Version 6 Addressing Architecture") (IPv6).

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = new IP('127.0.0.1');
$ip->isLoopback(); // bool(true)
```

### Multicast

Whether the IP is a multicast address according to
[RFC 3171](https://tools.ietf.org/html/rfc3171 "IANA Guidelines for IPv4
Multicast Address Assignments") (IPv4) or
[RFC 2373](https://tools.ietf.org/html/rfc2373 "IP Version 6 Addressing
Architecture") (IPv6).

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = new IP('127.0.0.1');
$ip->isMulticast(); // bool(false)
```

### Private Use

Whether the IP is for private use according to
[RFC 1918](https://tools.ietf.org/html/rfc1918 "Address Allocation for Private
Internets") (IPv4) or [RFC 4193](https://tools.ietf.org/html/rfc4193 "Unique
Local IPv6 Unicast Addresses") (IPv6).

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = new IP('127.0.0.1');
$ip->isPrivateUse(); // bool(false)
```

### Unspecified

Whether the IP is unspecified according to
[RFC 5735](https://tools.ietf.org/html/rfc5735 "Special Use IPv4 Addresses")
(IPv4) or [RFC 2373](https://tools.ietf.org/html/rfc2373 "IP Version 6
Addressing Architecture") (IPv6).

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = new IP('127.0.0.1');
$ip->isUnspecified(); // bool(false)
```
