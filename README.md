# IP Address [![Build Status](https://travis-ci.org/darsyn/ip.svg?branch=master)](https://travis-ci.org/darsyn/ip)

IP is an immutable value object that provides several notations of the same IP value, including some helper functions
for broadcast and network addresses, and whether its within the range of another IP address according to a CIDR
(subnet mask).

Although it deals with both IPv4 and IPv6 notations, it makes no distinction between the two protocol formats as it
converts both of them to a 16-byte binary sequence for easy mathematical operations and consistency (for example,
storing both IPv4 and IPv6 in the same column in a database).

## Installation

Use [Composer](http://getcomposer.org):

```bash
composer require darsyn/ip
```

### Requirements

At the moment, this library depends on in-built IP functions, `inet_ntop` and `inet_pton`; due to this fact, the library
will not be able to handle IPv6 addresses on 32-bit systems.

## Example Usage

```php
<?php

use Darsyn\IP\IP;

/**
 * Basic Usage
 */

$ip = new IP('12.34.56.78');
$ip->getShortAddress();        // string(11) "12.34.56.78"
$ip->getLongAddress();         // string(23) "0000:0000:0000:0000:0000:0000:0c22:384e"
$ip->getVersion();             // int(4)
$ip->isVersion(IP::VERSION_6); // bool(false)

// The IP address is stored inside the object as a 16-byte binary sequence. To access
// that use either the getBinary() method, or the __toString() magic method.

$binary = $ip->getBinary();
$binary = (string) $ip;

/**
 * Static Helper
 */

IP::validate('192.168.0.1');          // bool(true)
IP::validate('256.168.0.1');          // bool(false)
IP::validate('2001:4860:4860::8844'); // bool(true)
IP::validate('2001:4860:4860:8844');  // bool(false)

/**
 * Caveats
 */

// isVersion() and getVersion() use the 16-byte binary sequence to determine the IP
// address version, *NOT* the protocol notation that it was in when supplied to the
// constructor. This may cause confusion when you supply some IPv6 addresses - such
// as "::1" (the IPv6 notation for localhost) which would be reported as a version 4
// address.
$ip = new IP('::c22:384e');
$ip->getShortAddress();            // string(11) "12.34.56.78"
$ip->getVersion();                 // int(4)
$ip->isVersion(IP::VERSION_6);     // bool(false)

// Anyone who has worked with CIDR notation before will be used to a subnet mask
// between 0 and 32. However, because this library deals with IPv4 and IPv6
// interchangeably the subnet mask ranges from 0 to 128 instead. When working with
// IPv4 addresses, you must add 96 to the IPv4 subnet mask (therefore making it an
// IPv6 subnet mask) to get the correct integer to pass to the following methods.
$clientIp = new IP('12.48.183.1');
$clientIp->inRange($ip, 96 + 11);  // bool(true)
$clientIp->inRange($ip, 96 + 24);  // bool(false)

/**
 * Advanced
 */

$ip = new IP('12.34.56.78');

// Get the network address of an IP address given a subnet mask.
$networkIp = $ip->getNetworkIP(96 + 19);
$networkIp->getShortAddress();   // string() "12.34.32.0"

// Get the broadcast address of an IP address given a subnet mask.
$broadcastIp = $ip->getBroadcastIp(96 + 19);
$broadcastIp->getShortAddress(); // string() "12.34.63.255"
```

## Doctrine Support

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

## License

Please see the [separate license file](LICENSE.md) included in this repository for a full copy of the MIT license,
which this project is licensed under.

## Authors

- [Zander Baldwin](https://zanderbaldwin.com).

If you make a contribution (submit a pull request), don't forget to add your name here!
