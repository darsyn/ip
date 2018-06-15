## Helper Methods

Helper methods are for working with IP address and CIDR subnet masks.

Since IP objects are meant to be immutable, whenever an IP is returned it is returned
as a *new* instance of [`IpInterface`](../src/IpInterface.php) rather than modifying
the existing object - they are also returned as a static instance meaning an `IPv4`
object would return a new `IPv4` object, an `IPv6` returns `IPv6`, etc.

### CIDR (Subnet Mask)

All the helper methods require a CIDR value. Anyone who has worked with CIDR
notation before will most likely be used to a subnet mask between 0 and 32. However,
since this library deals with both IPv4 and IPv6 the CIDR values can range up to 128.

Instances of [`IPv4`](../src/Version/IPv4.php) will always deal with CIDR values between 0 and 32.

Instances of [`IPv6`](../src/Version/IPv6.php) will always deal with CIDR values between 0 and 128.

Instances of [`Multi`](../src/Version/Multi.php) will:
- Detect if the IP address is a version 4 address (according to the embedding strategy).
- If version 4 and the CIDR is less or equal to 32, attempt the method as if it
  was called from an `IPv4` instance.
- Otherwise (or the previous step resulted in an error/exception) attempt the
  method as if it was called from an `IPv6` instance.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

// IP is version 4 address and CIDR is <= 32. Uses IPv4::getNetworkIp().
IP::factory('127.0.0.1')->getNetworkIp(26);

// IP is version 4 address but CIDR is more than 32. Uses IPv6::getNetworkIp().
IP::factory('127.0.0.1')->getNetworkIp(107);

// IP is version 6 address. Uses IPv6::getNetworkIp().
IP::factory('2001:db8::a60:8a2e:0:7334')->getNetworkIp(50);
```

Methods that deal with CIDRs throw an [`InvalidCidrException`](../src/Exception/InvalidCidrException.php)
when a CIDR value that is out of range is passed. Out of range values are any value that is:
- Not an integer,
- Below zero,
- Above 32 (for `IPv4`), or
- Above 128 (for `IPv6` and `Multi`).

```php
<?php
use Darsyn\IP\Exception;
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('127.0.0.1');
try {
    $network = $ip->getNetworkIp(129);
} catch (Exception\InvalidCidrException $e) {
    echo sprintf(
        '"%d" is not a valid CIDR value!',
        $e->getSuppliedCidr()
    );
}
```

### `inRange()`

> `inRange(IpInterface $ip, int $cidr): bool`

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$hostIp = IP::factory(':ffff:c22:384e');
$clientIp = IP::factory('12.48.183.1');

$clientIp->inRange($hostIp, 11); // bool(true)
$clientIp->inRange($hostIp, 24); // bool(false)
```

### `getNetworkIp()`

> `getNetworkIp(int $cidr): IpInterface`


```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('12.34.56.78');
// Get the network address of an IP address given a subnet mask.
$networkIp = $ip->getNetworkIp(19);
$networkIp->getProtocolAppropriateAddress(); // string("12.34.32.0")

$ip === $networkIp; // bool(false)
```

### `getBroadcastIp()`

> `getBroadcastIp(int $cidr): bool`

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::factory('12.34.56.78');
// Get the broadcast address of an IP address given a subnet mask.
$broadcastIp = $ip->getBroadcastIp(19);
$broadcastIp->getProtocolAppropriateAddress(); // string("12.34.63.255")

$ip === $broadcastIp; // bool(false)
```
