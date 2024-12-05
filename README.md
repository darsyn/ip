IP is an immutable value object for (both version 4 and 6) IP addresses. Several
helper methods are provided for ranges, broadcast and network addresses, subnet
masks, whether an IP is a certain type (defined by RFC's), etc.

This project aims for simplicity of use and any contribution towards that goal -
whether a bug report, modifications to the codebase, or an improvement to the
accuracy or readability of the documentation - are always welcome.

# Documentation

Full documentation is available in the [`docs/`](docs/) folder.

## Compatibility

This library has extensive test coverage using PHPUnit on PHP versions: `5.6`,
`7.0`, `7.1`, `7.2`, `7.3`, `7.4`, `8.0`, `8.1`, `8.2`, `8.3` and `8.4`.

Static analysis is performed with PHPStan at `max` level on PHP `8.4`, using
core, bleeding edge, and deprecation rules.

> The Doctrine features for this library have been split off into their own
> package, [`darsyn/ip-doctrine`](https://packagist.org/packages/darsyn/ip-doctrine).

## Brief Example

- There are three main classes: [`IPv4`](src/Version/IPv4.php),
  [`IPv6`](src/Version/IPv6.php), and [`Multi`](src/Version/Multi.php) (for both
  version 4 and 6 addresses).
- Objects are created using a static factory method
  [`IpInterface::factory()`](src/IpInterface.php) instead of the constructor to
  speed up internal processes.
- When using `Multi`, the default strategy for representing version 4 addresses
  internally is [IPv4-mapped](docs/05-strategies.md).

```php
<?php declare(strict_types=1);

use Darsyn\IP\Exception;
use Darsyn\IP\Version\IPv4;

try {
    $ip = IPv4::factory('192.168.0.1');
} catch (Exception\InvalidIpAddressException $e) {
    exit('The IP address supplied is invalid!');
}

$companyNetwork = IPv4::factory('216.58.198.174');
if (!$ip->inRange($companyNetwork, 25)) {
    throw new \Exception('Request not from a known company IP address.');
}

// Is it coming from the local network?
if (!$ip->isPrivateUse()) {
    record_visit($ip->getBinary(), $_SERVER['HTTP_USER_AGENT']);
}
```

## Code of Conduct

This project includes and adheres to the [Contributor Covenant as a Code of
Conduct](CODE_OF_CONDUCT.md).

# License

Please see the [separate license file](LICENSE.md) included in this repository
for a full copy of the MIT license, which this project is licensed under.

# Authors

- [Zan Baldwin](https://zanbaldwin.com)
- [Jaume Casado Ruiz](http://jau.cat)
- [Pascal Hofmann](http://pascalhofmann.de)

If you make a contribution (submit a pull request), don't forget to add your
name here!
