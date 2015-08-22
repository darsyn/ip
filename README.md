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

## Example Usage

```php
<?php

use Darsyn\Ip\InternetProtocol as IP;

$ip = new IP('12.34.56.78');
$ip->getLongAddress(); //  string(23) "0:0:0:0:0:ffff:c22:384e"

$ip = new IP('0:0:0:0:0:ffff:c22:384e');
$ip->getShortAddress(); // string(11) "12.34.56.78"

$anotherIp = new IP('12.34.201.26');
$anotherIp->inRange($ip, 96 + 16); // bool(true)
$anotherIp->inRange($ip, 96 + 24); // bool(false)
```

**Note:** Anyone who has worked with IP subnet masks before will be used to the CIDR being a maximum of 32. However,
this library deals with IPv4 and IPv6 interchangably so the new maximum value for a CIDR is 128. In the example above we
added 96 to the old CIDR values to get the correct integer to use.

## License

Please see the [separate license file](LICENSE.md) included in this repository for a full copy of the MIT license,
which this project is licensed under.

## Authors

- [Zander Baldwin](https://zanderbaldwin.com).

If you make a contribution (submit a pull request), don't forget to add your name here!
