# Helper Methods

Helper methods are for working with IP address and CIDR subnet masks.

Since IP objects are meant to be immutable, whenever an IP is returned it is
returned as a *new* instance of `Darsyn\IP\IpInterface` rather than modifying
the existing object - they are also returned as a static instance meaning an
`IPv4` object would return a new `IPv4` object, an `IPv6` returns `IPv6`, etc.

## Concepts

### CIDR (Subnet Mask)

Most of the the helper methods require a CIDR value. Anyone who has worked with
CIDR notation before will most likely be used to a subnet mask between 0 and 32.
However, since the `Multi` class deals with both IPv4 and IPv6 the CIDR values
can range up to 128.

Instances of `IPv4` will always deal with CIDR values between 0 and 32.

Instances of `IPv6` will always deal with CIDR values between 0 and 128.

Instances of `Multi` will:

- Detect if the IP address is a version 4 address (according to the embedding
  strategy).
- If version 4 and the CIDR is less or equal to 32, attempt the method as if it
  was called from an `IPv4` instance.
- Otherwise, attempt the method as if it was called from an `IPv6` instance.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

// IP is version 4 address and CIDR is <= 32. Uses IPv4::getNetworkIp().
IP::factory('127.0.0.1')->getNetworkIp(26);

// IP is version 4 address but CIDR is more than 32. Uses IPv6::getNetworkIp().
IP::factory('127.0.0.1')->getNetworkIp(107);

// IP is version 6 address. Uses IPv6::getNetworkIp(), even though CIDR is below 32.
IP::factory('2001:db8::a60:8a2e:0:7334')->getNetworkIp(31);
```

A `Darsyn\IP\Exception\InvalidCidrException` is thrown whenever a CIDR value
that is out of range is supplied. Out of range values are any value that is:

- Not an integer,
- Below zero,
- Above 32 (for `IPv4`), or
- Above 128 (for `IPv6` and `Multi`).

### IP Comparison

Some of the helper methods accept another IP address to work against. To
successfully compare IP address, both must be the same byte-length:

- `IPv4` instances can only be compared against other, as internally they are
  represented as 4-byte strings.
- `IPv6` and `Multi` can be compared against themselves and each other, as
  internally they are represented as 16-byte strings.

When using the `Multi` class to call the helper method, it will attempt the
method as if it was called from an `IPv4` instance if:

- Both are IPv4-embedded addresses, and
- Both are embedded using the same [embedding strategy](./05-strategies.md).

Otherwise, `Multi` attempts the method as if it was called from an `IPv6`
instance.

### Examples

```php
<?php

// IPv6 operation: helper method called from IPv6.
IPv6::factory('ffff:7f00:1')->inRange(Multi::factory('127.0.0.1'), 23);

// IPv4 operation: called from Multi, both IPs are IPv4-embedded, CIDR below 32.
Multi::factory('127.0.0.1')->inRange(Multi::factory('127.0.0.1'), 23);

// IPv6 operation: both IPv4-embedded, but CIDR above 32.
Multi::factory('127.0.0.1')->inRange(Multi::factory('127.0.0.1'), 35);

// IPv6 operation: supplied IP is not IPv4-embedded.
Multi::factory('127.0.0.1')->inRange(Multi::factory('d6be:583:b07a::c7'), 23);

// IPv6 operation: embedding strategies do not match.
Multi::factory('127.0.0.1', new Mapped)
    ->inRange(Multi::factory('127.0.0.1', new Derived), 23);

// WrongVersionException: Multi and IPv4 are different byte lengths.
Multi::factory('127.0.0.1')->inRange(IPv4::factory('127.0.0.1'), 23);

// InvalidCidrException: CIDR above 128.
Multi::factory('127.0.0.1')->inRange(Multi::factory('127.0.0.1'), 129);
```

## Method Reference

### Network IP

> ```
> @throws \Darsyn\IP\Exception\InvalidCidrException
>
> getNetworkIp(int $cidr): IpInterface
> ```


```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('12.34.56.78');
// Get the network address of an IP address given a subnet mask.
$networkIp = $ip->getNetworkIp(19);
$networkIp->getProtocolAppropriateAddress(); // string("12.34.32.0")
```

### Broadcast IP

> ```
> @throws \Darsyn\IP\Exception\InvalidCidrException
>
> getBroadcastIp(int $cidr): bool
> ```

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('12.34.56.78');
// Get the broadcast address of an IP address given a subnet mask.
$broadcastIp = $ip->getBroadcastIp(19);
$broadcastIp->getProtocolAppropriateAddress(); // string("12.34.63.255")
```

### Is IP in Range?

> ```
> @throws \Darsyn\IP\Exception\WrongVersionException
> @throws \Darsyn\IP\Exception\InvalidCidrException
>
> inRange(IpInterface $ip, int $cidr): bool
> ```

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$hostIp = IP::factory(':ffff:c22:384e');
$clientIp = IP::factory('12.48.183.1');

$clientIp->inRange($hostIp, 11); // bool(true)
$clientIp->inRange($hostIp, 24); // bool(false)
```

### Greatest Common CIDR

> ```
> @throws \Darsyn\IP\Exception\WrongVersionException
>
> getCommonCidr(IpInterface $ip): int
> ```

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$hostIp = IP::factory('d6be:583:71a4:aa6d:c77d:77dd:cec:f897');
$clientIp = IP::factory('d6be:583:71a4:aa67:b07a::c7');
$hostIp->getCommonCidr($clientIp); // int(60)
```

## `IPv6` vs `Multi`?

The `Multi` class tries to deal with both IPv4 and IPv6 interchangeably which
can lead to some unexpected results if an IPv6 address is detected as an
IPv4-embedded address. Valid CIDR values can be either 0-32 or 0-128 depending
on internal state and the embedding strategy used.

All the helper methods of `Multi` are affected by this.

The `IPv6` class, however, gives consistent results regardless of embedding
strategy and always deals with CIDR values from 0 to 128.

### IPv6 From Embedded

If you want to embed IPv4 addresses into IPv6, but do not want `Multi` to return
varying results depending on whether an IPv4 address is embedded or not, then
use `IPv6::fromEmbedded()`.

It accepts both IPv4 and IPv6 addresses, embeds IPv4 addresses into IPv6
according to the embedding strategy, and from that point on treats it purely as
an IPv6 address.

```php
<?php
use Darsyn\IP\Strategy\Mapped;
use Darsyn\IP\Version\Ipv6;

// Strategy is optional; defaults to Mapped unless
// Multi::setDefaultEmbeddingStrategy() called previously.
$ip = IPv6::fromEmbedded('127.0.0.1', new Mapped);
$ip->getCompactedAddress(); // string("::ffff:7f00:1")

try {
    $ip->getDotAddress();
} catch (\Error $e) {
    // IPv6 addresses are not considered IPv4 addresses and
    // therefore do not have the method getDotAddress().
}
```

> Please note that calling `Multi::fromEmbedded()` returns an instance of
> `Multi` and effectively is the same as calling the factory method.
