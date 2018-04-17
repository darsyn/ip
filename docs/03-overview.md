## Overview

IP addresses get automatically validated on object instantiation; if the IP
address supplied is invalid, an [`InvalidIpAddressException`](../src/Exception/InvalidIpAddressException.php)
will be thrown.

```php
<?php
use Darsyn\IP\Version\IPv4;
use Darsyn\IP\Exception;

try {
    $ip = new IPv4('127.0.0.1');
} catch (Exception\InvalidIpAddressException $e) {
    echo 'The IP address supplied is invalid!';
}
```

### Versions

This library can work with version 4 addresses, version 6 addresses, or both formats
interchangeably using the classes [`IPv4`](../src/Version/IPv4.php),
[`IPv6`](../src/Version/IPv6.php) and [`Multi`](../src/Version/Multi.php) respectively.

All versions implement [`IpInterface`](../src/IpInterface.php), along with extra interfaces for each version:
- `IPv4` implements [`Version4Interface`](../src/Version/Version4Interface.php),
- `IPv6` implements [`Version6Interface`](../src/Version/Version6Interface.php),
- `Multi` implements [`MultiVersionInterface`](../src/Version/MultiVersionInterface.php)
  (which in turn implements *both* `Version4Interface` and `Version6Interface`).

If you try to use an version 6 address with the `IPv4` class, or an version 4 address with
the `IPv6` class, then a [`WrongVersionException`](../src/Exception/WrongVersionException.php)
will be thrown.

```php
<?php
use Darsyn\IP\Version\IPv4;
use Darsyn\IP\Exception;

try {
    $ip = new IPv4('::1');
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

- `$ip->getVersion()` returns the IP address version (either `int(4)` or `int(6)`). 
- `$ip->isVersion($version)` returns a boolean value on whether the `$ip` object
  is the version specified in `$version` (which must be either `int(4)` or `int(6)`).
- `$ip->isVersion4()` returns a boolean value on whether the `$ip` object contains a version 4 address.
- `$ip->isVersion6()` returns a boolean value on whether the `$ip` object contains a version 6 address.

> **Note:** When using the `Multi` class, the address version is determined by
> what [embedding strategy](./strategies.md) is used rather than what notation
> was passed to the constructor.

### Return Formats

Once an IP object has been initialised, the IP address value can be returned in either
human-readable format or in binary.

This binary string will *always* be 4 bytes long when using `IPv4` and 16 bytes
long when using `IPv6` and `Multi`.

Human-readable format comes in 3 flavours:
- Dot notation is for IPv4 addresses, eg `127.0.0.1`.
- Compacted is for IPv6 addresses, eg `2001:db8::a60:8a2e:370:7334`.
- Expanded is for IPv6 addresses, eg `2001:0db8:0000:0000:0a60:8a2e:0370:7334`.

#### `getDotAddress()`

Is only available for `IPv4` and `Multi` classes. Calling `getDotAddress()` on
an instance of `Multi` that contains a version 6 address will result in a
`WrongVersionException` being thrown.

```php
<?php
use Darsyn\IP\Version\Multi as IP;
use Darsyn\IP\Exception;

$ip = new IP('127.0.0.1');

try {
    echo $ip->getDotAddress(); // string("127.0.0.1")
} catch (Exception\WrongVersionException $e) {
    echo 'Cannot convert a version 6 address to dot-notation!';
}
```

#### `getCompactedAddress()`

Is only available for `IPv6` and `Multi` classes. Calling `getCompactedAddress()`
on an instance of `Multi` that contains a version 4 address will result in the IP
address being converted to a version 6 address according to the embedding strategy.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = new IP('127.0.0.1');
echo $ip->getCompactedAddress(); // string("::ffff:7f00:1")
```

#### `getExpandedAddress()`

Is only available for `IPv6` and `Multi` classes. Calling `getExpandedAddress()`
on an instance of `Multi` that contains a version 4 address will result in the IP
address being converted to a version 6 address according to the embedding strategy.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = new IP('127.0.0.1');
$ip->getExpandedAddress(); // string("0000:0000:0000:0000:0000:ffff:7f00:0001")
```

#### `getProtocolAppropriateAddress()`

Is only available for the `Multi` class. If the instance of `Multi` contains a
version 4 address, it will be returned in dot-notation, otherwise it returns a
compacted version 6 address.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = new IP('::ffff:7f00:1');
$ip->getProtocolAppropriateAddress(); // string("127.0.0.1")
```

#### `getBinary()`

Returns the 16 byte (4 bytes if using `IPv4`) binary string of the IP address.
This will most likely contain non-printable characters, so is not appropriate
for displaying. 

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = new IP('127.0.0.1');
$binary = $ip->getBinary();
```

#### String Casting

Casting the IP object to a string is the equivalent of calling `getBinary()`. Whilst
this may not be the most useful when dumping the object, it's the only method that
consistently available is all classes.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = new IP('127.0.0.1');
$binary = (string) $ip;
```


