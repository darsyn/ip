# Overview

IP addresses get automatically validated on creation through the static factory
method; if the IP address supplied is invalid an `InvalidIpAddressException`
will be thrown.

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

## Instantiation

All classes are instantiated using the `factory()` static method. This method
validates the input and converts it into binary. In the case of the `Multi`
class it also packs any version 4 addresses into a version 6 address.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

try {
    $ip = new IP('127.0.0.1');
} catch (\Error) {
    echo 'Cannot create IP using "new"; please use IP::factory() instead.';
}
```

> Many instances are constructed for all [helper](./04-helpers.md) and
> [type](./07-types.md) methods. Validating the input every time a new instance
> is constructed slows things down considerably, so to speed up internal
> processes the constructor does not perform any input validation. Because of
> this the constructor method has been kept private.

## Return Formats

Once an IP object has been initialised, the IP address value can be returned in
either human-readable format or in binary.

This binary string will *always* be 4 bytes long when using `IPv4` and 16 bytes
long when using `IPv6` and `Multi`.

Human-readable format comes in 3 flavours:

- Dot notation is for IPv4 addresses, eg `127.0.0.1`.
- Compacted is for IPv6 addresses, eg `2001:db8::a60:8a2e:370:7334`.
- Expanded is for IPv6 addresses, eg `2001:0db8:0000:0000:0a60:8a2e:0370:7334`.

### Dot Address

`getDotAddress()` is only available for `IPv4` and `Multi` classes. Calling
`getDotAddress()` on an instance of `Multi` that contains a version 6 address
will result in a `WrongVersionException` being thrown.

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

### Compacted Address

`getCompactedAddress()` is only available for `IPv6` and `Multi` classes.
Calling `getCompactedAddress()` on an instance of `Multi` that contains a
version 4 address will result in the IP address being converted to a version 6
address according to the embedding strategy.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('127.0.0.1');
echo $ip->getCompactedAddress(); // string("::ffff:7f00:1")
```

### Expanded Address

`getExpandedAddress()` is only available for `IPv6` and `Multi` classes. Calling
`getExpandedAddress()` on an instance of `Multi` that contains a version 4
address will result in the IP address being converted to a version 6 address
according to the embedding strategy.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('127.0.0.1');
$ip->getExpandedAddress(); // string("0000:0000:0000:0000:0000:ffff:7f00:0001")
```

### Protocol Appropriate Address

`getProtocolAppropriateAddress()` is only available for the `Multi` class. If
the instance of `Multi` contains a version 4 address, it will be returned in
dot notation, otherwise it returns a compacted version 6 address.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('::ffff:7f00:1');
$ip->getProtocolAppropriateAddress(); // string("127.0.0.1")
```

### Binary

`getBinary()` returns the 16 byte (4 bytes if using `IPv4`) binary string of the
IP address. This will most likely contain non-printable characters, so is not
appropriate for displaying. 

```php
<?php
use Darsyn\IP\Version\IPv4 as IP;

// The IPv4 address "80.111.111.112" just so happens to be, when converted to
// binary, the same as the binary for the ASCII string "Poop". Today you learnt
// something new.

$ip = IP::factory('80.111.111.112');
$ip->getBinary(); // string("Poop")
```

## String Casting

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
