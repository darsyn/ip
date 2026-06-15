# Formatters

Internally, this library uses the PHP functions `inet_pton` and `inet_ntop`.
However the `inet_ntop` function formats some IP addresses in an inconsistent
and non-standard way (for example, the IP address `::ffff:c22:384e` would be
returned as `::ffff:12.34.56.78` when converting its binary representation to
human-readable protocol via `inet_ntop`).

This library provides a pure-PHP implementation called
`Darsyn\IP\Formatter\ConsistentFormatter` to return IP addresses in the correct
format, which is used by default.

However should you wish to use the native implementation for any reason, you
may set the `Darsyn\IP\Formatter\NativeFormatter` globally:

```php
<?php
use Darsyn\IP\Formatter\NativeFormatter;
use Darsyn\IP\Version\Multi as IP;

IP::setProtocolFormatter(new NativeFormatter);
$ip = IP::factory('::ffff:c22:384e');
$ip->getCompactedAddress(); // string("::ffff:12.34.56.78")
```

## Per-call formatter
`setProtocolFormatter()` changes the formatter globally for every IP object, the
formatting methods therefore accept an optional formatter as their
first argument, overriding the global formatter for that call alone:
- `Darsyn\IP\Version\IPv4::getDotAddress()`
- `Darsyn\IP\Version\IPv6::getCompactedAddress()`
- `Darsyn\IP\Version\Multi::getDotAddress()`
- `Darsyn\IP\Version\Multi::getProtocolAppropriateAddress()`

```php
<?php
use Darsyn\IP\Formatter\ConsistentFormatter;
use Darsyn\IP\Formatter\NativeFormatter;
use Darsyn\IP\Version\IPv6 as IP;

IP::setProtocolFormatter(new ConsistentFormatter());
$ip = IP::factory('::ffff:c22:384e');

$ip->getCompactedAddress();                      // string("::ffff:c22:384e")
$ip->getCompactedAddress(new NativeFormatter);   // string("::ffff:12.34.56.78")
$ip->getCompactedAddress();                      // string("::ffff:c22:384e")
```

Passing any value that does not implement
`Darsyn\IP\Formatter\ProtocolFormatterInterface` will trigger a deprecation notice and fall back to the global formatter.
