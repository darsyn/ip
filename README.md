[![Build Status](https://travis-ci.org/darsyn/ip.svg?branch=master)](https://travis-ci.org/darsyn/ip)

IP is an immutable value object for (both version 4 and 6) IP addresses. Several
helper methods are provided for ranges, broadcast and network addresses, subnet
masks, whether an IP is a certain type (defined by RFC's), etc.

# Where's the documentation?

This is the second beta release for `4.0.0` before the stable release. It is not
intended for production. Please see the
[`master` branch](https://github.com/darsyn/ip/tree/master) for the
[latest documentation](https://github.com/darsyn/ip/blob/master/README.md) and
[stable release](https://github.com/darsyn/ip/releases/tag/3.3.1).

Complete documentation has been written for the upcoming `4.0.0` release and
[can be found in the `docs/` folder](docs/).

# I want to test `4.0.0-beta2`!

The library is fairly similar to how it was in `3.3.1` with the following
differences:

- Firstly, there are three main classes instead of one:
  [`IPv4`](src/Version/IPv4.php), [`IPv6`](src/Version/IPv6.php), and
  [`Multi`](src/Version/Multi.php) (for both version 4 and 6 addresses).
- Secondly, objects are created using a static factory method instead of the
  constructor.
- A few methods have been renamed (see [the API reference](docs/09-api.md)).
- Finally, the default for representing version 4 addresses internally has
  changed from IPv4-compatible to IPv4-mapped.

```php
<?php declare(strict_types=1);
use Darsyn\IP\Version\Multi as IP;
use Darsyn\IP\Exception;

try {
    $ip = IP::factory('127.0.0.1');
} catch (Exception\InvalidIpAddressException $e) {
    echo 'The IP address supplied is invalid!';
}
```

# License

Please see the [separate license file](LICENSE.md) included in this repository
for a full copy of the MIT license, which this project is licensed under.

# Authors

- [Zan Baldwin](https://zanbaldwin.com)
- [Jaume Casado Ruiz](http://jau.cat)
- [Pascal Hofmann](http://pascalhofmann.de)

If you make a contribution (submit a pull request), don't forget to add your
name here!
