## Formatters

Internally, this library uses the PHP functions `inet_pton` and `inet_ntop`.
However the `inet_ntop` function formats some IP addresses in an inconsistent
and non-standard way (for example, the IP address `::ffff:c22:384e` would be 
returned as `::ffff:12.34.56.78` when converting it's binary representation to
human-readable protocol via `inet_ntop`).

This library provides a pure-PHP implementation called
[`ConsistentFormatter`](../src/Formatter/ConsistentFormatter.php) to return IP
addresses in the correct format, which is used by default.

However should you wish to use the native implementation for any reason, you
may set the [`NativeFormatter`](../src/Formatter/NativeFormatter.php) globally:

```php
<?php
use Darsyn\IP\Formatter\NativeFormatter;
use Darsyn\IP\Version\Multi as IP;

IP::setProtocolFormatter(new NativeFormatter);
$ip = new IP('::ffff:c22:384e');
$ip->getCompactedAddress(); // string("::ffff:12.34.56.78")
```
