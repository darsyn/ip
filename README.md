[![Build Status](https://travis-ci.org/darsyn/ip.svg?branch=master)](https://travis-ci.org/darsyn/ip)

IP is an immutable value object for (both version 4 and 6) IP addresses. Several helper methods are provided for ranges, broadcast and network addresses, subnet masks, whether an IP is a certain type (defined by RFC's), etc.

Although it deals with both IPv4 and IPv6 notations, it makes no distinction between the two protocol formats as it converts both of them to a 16-byte binary sequence for easy mathematical operations and consistency (for example, storing both IPv4 and IPv6 in the same column in a database).

# Installation

Use [Composer](http://getcomposer.org):

```bash
composer require darsyn/ip
```

### Requirements

On PHP 5.4, strings converted from IP addresses that end with a series of nul-characters (such as `fd0a:238b:4a96::`) get truncated, meaning that the converted binary sequence is not valid. Due to this, only PHP versions 5.5+ are supported.

Secondly, this library cannot handle IPv6 addresses on 32-bit systems due to a dependency on the in-built PHP functions `inet_pton` and `int_ntop`, though a work-around may be provided in the future.

# Doctrine Support

This library can be used to support IP address as column types with Doctrine:

```php
<?php

use Doctrine\DBAL\Types\Type;

Type::addType('ip', 'Darsyn\IP\Doctrine\IpType');
```

If you are using [Symfony](http://symfony.com), then add the following to your main configuration:

```yaml
doctrine:
    dbal:
        types:
            ip: Darsyn\IP\Doctrine\IpType
```

Now you can happily store IP addresses in your entites like nobody's business:

```php
<?php

use Darsyn\IP\IP;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AnalyticsEntity
{
    /**
     * @ORM\Column(type="ip")
     */
    protected $ipAddress;

    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    public function setIpAddress(IP $ip)
    {
        $this->ipAddress = $ip;
    }
}
```

# Documentation

## Instantiation

IP addresses get automatically validated on object instantiation; if the IP address supplied is invalid, an [`InvalidIpAddressException`](src/InvalidIpAddressException.php) will be thrown.

```php
<?php
use Darsyn\IP\IP;
use Darsyn\IP\InvalidIpAddressException;

try {
    $ip = new IP('127.0.0.1');
} catch (InvalidIpAddressException $e) {
    echo 'The IP address supplied is invalid!';
}
```

## Version

The IP address version can be checked with `getVersion()`, `isVersion()`, `isVersion4()`, and `isVersion6()`.
The methods use the 16-byte binary sequence to determine the IP address version, *not* the protocol notation that it was in when supplied to the constructor. This may cause confusion when you supply some IPv6 addresses &mdash; such as `::1` (the IPv6 notation for localhost) which would be reported as a version 4 address.

The values for each IP version type can be found in the constants `VERSION_4` and `VERSION_6`.

```php
<?php
use Darsyn\IP\IP;

$ip = new IP('127.0.0.1');

$ip->getVersion();             // int(4)
$ip->isVersion(IP::VERSION_4); // bool(true)
$ip->isVersion(IP::VERSION_6); // bool(false)
$ip->isVersion4();             // bool(true)
$ip->isVersion6();             // bool(false)
```

## IP Return Formats.

once an object has been instantiated, the value can be returned as either long, short or binary notation.

Long notation is the IP address in full (expanded) IPv6 format (regardless of whether the value is a version 4 or 6 IP address).
Short notation is the IP address returned in its appropriate version notation, with IPv6 addresses shortened into condensed notation (such as `::c22:384e`).
Binary notation is a 16-byte string representing the IP address; most the time this will not be ASCII-safe.

Casting the IP address object to a string will return it in binary notation (the same as calling the `getBinary()` method).

```php
<?php
use Darsyn\IP\IP;

$ip = new IP('127.0.0.1');
$ip->getShortAddress(); // string(11) "127.0.0.1"
$ip->getLongAddress();  // string(23) "0000:0000:0000:0000:0000:0000:7f00:0001"
$ip->getBinary();
$binaryValue = (string) $ip;
```

## Helper Methods

Some helper methods return an instance of [`Darsyn\IP\IP`](src/IP.php). Since it is an immutable value object these methods return *new* instances rather than modifying the existing object.

#### CIDR (Subnet Mask)

Some helper methods take a CIDR as a second argument. Anyone who has worked with CIDR notation before will be used to a subnet mask between 0 and 32. However, because this library deals with IPv4 and IPv6 interchangeably the subnet mask ranges from 0 to 128 instead. When working with IPv4 addresses, you must add 96 to the IPv4 subnet mask (therefore making it an IPv6 subnet mask) to get the correct integer to pass to the following methods.

### In Range

```php
<?php
use Darsyn\IP\IP;

$hostIp = new IP('::c22:384e');
$clientIp = new IP('12.48.183.1');

$clientIp->inRange($ip, 96 + 11); // bool(true)
$clientIp->inRange($ip, 96 + 24); // bool(false)
```

### Network IP

```php
<?php
use Darsyn\IP\IP;

$ip = new IP('12.34.56.78');
// Get the network address of an IP address given a subnet mask.
$networkIp = $ip->getNetworkIP(96 + 19);
$networkIp->getShortAddress(); // string() "12.34.32.0"
```

### Broadcast IP

```php
<?php
use Darsyn\IP\IP;

$ip = new IP('12.34.56.78');
// Get the broadcast address of an IP address given a subnet mask.
$broadcastIp = $ip->getBroadcastIp(96 + 19);
$broadcastIp->getShortAddress(); // string() "12.34.63.255"
```

### Type Methods

The type methods return a boolean value depending on whether the IP address is a certain type.

#### Link Local

Whether the IP is reserved for link-local usage according to [RFC 3927](https://tools.ietf.org/html/rfc3927 "Dynamic Configuration of IPv4 Link-Local Addresses") (IPv4) or [RFC 4291](https://tools.ietf.org/html/rfc4291 "IP Version 6 Addressing Architecture") (IPv6).

```php
<?php
use Darsyn\IP\IP;

$ip = new IP('127.0.0.1');
$ip->isLinkLocal(); // bool(false)
```

#### Loopback

Whether the IP is a loopback address according to [RFC 3330](https://tools.ietf.org/html/rfc3330 "Special-Use IPv4 Addresses") (IPv4) or [RFC 2373](https://tools.ietf.org/html/rfc2373 "IP Version 6 Addressing Architecture") (IPv6).

```php
<?php
use Darsyn\IP\IP;

$ip = new IP('127.0.0.1');
$ip->isLoopback(); // bool(true)
```

#### Multicast

Whether the IP is a multicast address according to [RFC 3171](https://tools.ietf.org/html/rfc3171 "IANA Guidelines for IPv4 Multicast Address Assignments") (IPv4) or [RFC 2373](https://tools.ietf.org/html/rfc2373 "IP Version 6 Addressing Architecture") (IPv6).

```php
<?php
use Darsyn\IP\IP;

$ip = new IP('127.0.0.1');
$ip->isMulticast(); // bool(false)
```

#### Private Use

Whether the IP is for private use according to [RFC 1918](https://tools.ietf.org/html/rfc1918 "Address Allocation for Private Internets") (IPv4) or [RFC 4193](https://tools.ietf.org/html/rfc4193 "Unique Local IPv6 Unicast Addresses") (IPv6).

```php
<?php
use Darsyn\IP\IP;

$ip = new IP('127.0.0.1');
$ip->isPrivateUse(); // bool(false)
```

#### Unspecified

Whether the IP is unspecified according to [RFC 5735](https://tools.ietf.org/html/rfc5735 "Special Use IPv4 Addresses") (IPv4) or [RFC 2373](https://tools.ietf.org/html/rfc2373 "IP Version 6 Addressing Architecture") (IPv6).

```php
<?php
use Darsyn\IP\IP;

$ip = new IP('127.0.0.1');
$ip->isUnspecified(); // bool(false)
```

# License

Please see the [separate license file](LICENSE.md) included in this repository for a full copy of the MIT license,
which this project is licensed under.

# Authors

- [Zander Baldwin](http://zanderbaldwin.com)
- [Jaume Casado Ruiz](http://jau.cat)
- [Pascal Hofmann](http://pascalhofmann.de)

If you make a contribution (submit a pull request), don't forget to add your name here!
