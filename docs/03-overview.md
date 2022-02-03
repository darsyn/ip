# Overview

IP addresses get automatically validated on object instantiation; if the IP
address supplied is invalid, an `InvalidIpAddressException` will be thrown.

```php
<?php
use Darsyn\IP\Version\IPv4;
use Darsyn\IP\Exception;

try {
    $ip = IPv4::factory('127.0.0.1');
} catch (Exception\InvalidIpAddressException $e) {
    echo 'The IP address supplied is invalid!';
}
```

## Versions

This library can work with version 4 addresses, version 6 addresses, or both
formats interchangeably using the classes `Darsyn\IP\Version\IPv4`,
`Darsyn\IP\Version\IPv6` and `Darsyn\IP\Version\Multi` respectively.

All versions implement `Darsyn\IP\IpInterface`, along with extra interfaces for
each version:

- `IPv4` implements `Darsyn\IP\Version\Version4Interface`,
- `IPv6` implements `Darsyn\IP\Version\Version6Interface`,
- `Multi` implements `Darsyn\IP\Version\MultiVersionInterface`
  (which in turn implements *both* `Version4Interface` and `Version6Interface`).

If you try to use a version 6 address with the `IPv4` class, or a version 4
address with the `IPv6` class, then a `Darsyn\IP\Exception\WrongVersionException`
will be thrown.

```php
<?php
use Darsyn\IP\Version\IPv4;
use Darsyn\IP\Exception;

try {
    $ip = IPv4::factory('::1');
} catch (Exception\WrongVersionException $e) {
    echo 'Only version 4 IP addresses are allowed!';
} catch (Exception\InvalidIpAddressException $e) {
    echo 'The IP address supplied is invalid!';
}
```

> **Note:** The `WrongVersionException` is provided to give finer control on
> handling errors. It extends `InvalidIpAddressException` so catching it isn't
> necessary.

Each class has methods for determining the version:

- `$ip->getVersion()` returns the IP address version (either `int(4)` or
  `int(6)`). 
- `$ip->isVersion($version)` returns a boolean value on whether the `$ip` object
  is the version specified in `$version` (which must be either `int(4)` or
  `int(6)`).
- `$ip->isVersion4()` returns a boolean value on whether the `$ip` object
  contains a version 4 address.
- `$ip->isVersion6()` returns a boolean value on whether the `$ip` object
  contains a version 6 address.

> **Note:** When using the `Multi` class, the address version is determined by
> what [embedding strategy](./05-strategies.md) is used rather than what
> notation was passed to the constructor.

## Return Formats

Once an IP object has been initialised, the IP address value can be returned in
either human-readable format or in binary.

This binary string will *always* be 4 bytes long when using `IPv4` and 16 bytes
long when using `IPv6` and `Multi`.

Human-readable format comes in 3 flavours:

- Dot notation is for IPv4 addresses, eg `127.0.0.1`.
- Compacted is for IPv6 addresses, eg `2001:db8::a60:8a2e:370:7334`.
- Expanded is for IPv6 addresses, eg `2001:0db8:0000:0000:0a60:8a2e:0370:7334`.

### `getDotAddress()`

Is only available for `IPv4` and `Multi` classes. Calling `getDotAddress()` on
an instance of `Multi` that contains a version 6 address will result in a
`WrongVersionException` being thrown.

```php
<?php
use Darsyn\IP\Version\Multi as IP;
use Darsyn\IP\Exception;

$ip = IP::factory('127.0.0.1');

try {
    echo $ip->getDotAddress(); // string("127.0.0.1")
} catch (Exception\WrongVersionException $e) {
    echo 'Cannot convert a version 6 address to dot-notation!';
}
```

### `getCompactedAddress()`

Is only available for `IPv6` and `Multi` classes. Calling `getCompactedAddress()`
on an instance of `Multi` that contains a version 4 address will result in the
IP address being converted to a version 6 address according to the embedding
strategy.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('127.0.0.1');
echo $ip->getCompactedAddress(); // string("::ffff:7f00:1")
```

### `getExpandedAddress()`

Is only available for `IPv6` and `Multi` classes. Calling `getExpandedAddress()`
on an instance of `Multi` that contains a version 4 address will result in the
IP address being converted to a version 6 address according to the embedding
strategy.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('127.0.0.1');
$ip->getExpandedAddress(); // string("0000:0000:0000:0000:0000:ffff:7f00:0001")
```

### `getProtocolAppropriateAddress()`

Is only available for the `Multi` class. If the instance of `Multi` contains a
version 4 address, it will be returned in dot-notation, otherwise it returns a
compacted version 6 address.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('::ffff:7f00:1');
$ip->getProtocolAppropriateAddress(); // string("127.0.0.1")
```

### `getBinary()`

Returns the 16 byte (4 bytes if using `IPv4`) binary string of the IP address.
This will most likely contain non-printable characters, so is not appropriate
for displaying. 

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('127.0.0.1');
$binary = $ip->getBinary();
```

### String Casting

Previous versions of this documentation specified that string casting for IP
objects was enabled to get the binary string, but that was unfortunately untrue.
Now, string casting is enabled for all version classes and the `__toString()`
method is promised in `Darsyn\IP\IpInterface`:

- String casting the `IPv4` class is the equivalent of `$ip->getDotAddress()`.
- String casting the `IPv6` class is the equivalent of
  `$ip->getCompactedAddress()`.
- String casting the `Multi` class is the equivalent of
  `$ip->getProtocolAppropriateAddress()`.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('::ffff:7f00:1');
$printableString = (string) $ip; // string("127.0.0.1")
```

## What's Wrong with `Multi`?

The `Multi` class tries to deal with both IPv4 and IPv6 interchangeably which
can lead to some unexpected results. For example, if you embed the IPv4 address
`12.34.56.78` into an IPv6 address you get `::ffff:c22:384e` (using the [Mapped
strategy](./05-strategies.md)).

If you want to get the broadcast address using a CIDR of 19, the result
completely depends on whether you view this IP address to be IPv4 or IPv6. If
assuming IPv4, then the resulting broadcast address will be `::ffff:c22:3fff`
(the internal representation of the embedded IPv4 address `12.34.63.255`).
However, if assuming IPv6 then the resulting broadcast address will be
`::1fff:ffff:ffff:ffff:ffff:ffff:ffff`.

Several methods of `Multi` detect if an IPv4 address has been embedded, they
will then attempt to perform the actions on the embedded IPv4 address first
(returning the IPv4 result embedded into another IPv6), and only then default to
working on the entire IPv6 address if that fails.

The following methods of `Multi` are all affected by the presence of an embedded
IPv4 address:

- `getNetworkIp()` (if CIDR is 32 or below).
- `getBroadcastIp()` (if CIDR is 32 or below).
- `inRange()` (if CIDR is 32 or below, and the other IP is also an _embedded_
  IPv4).
- `getCommonCidr()` (if the other IP is also an _embedded_ IPv4).

### `IPv6::fromEmbedded()`

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
