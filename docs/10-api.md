# API Reference

| Method                                | Returns              | IPv4 | IPv6 | Multi |
|---------------------------------------|----------------------|------|------|-------|
| `factory(string $ip, [$strategy])`    | Static `IpInterface` | ✓    | ✓    | ✓     |
| `getBinary()`                         | `string`             | ✓    | ✓    | ✓     |
| `getVersion()`                        | `int`                | ✓    | ✓    | ✓     |
| `isVersion(int $version)`             | `bool`               | ✓    | ✓    | ✓     |
| `isVersion4()`                        | `bool`               | ✓    | ✓    | ✓     |
| `isVersion6()`                        | `bool`               | ✓    | ✓    | ✓     |
| `getNetworkIp(int $cidr)`             | Static `IpInterface` | ✓    | ✓    | ✓     |
| `getBroadcastIp(int $cidr)`           | Static `IpInterface` | ✓    | ✓    | ✓     |
| `inRange(IpInterface $ip, int $cidr)` | `bool`               | ✓    | ✓    | ✓     |
| `isMapped()`                          | `bool`               | ✓    | ✓    | ✓     |
| `isDerived()`                         | `bool`               | ✓    | ✓    | ✓     |
| `isCompatible()`                      | `bool`               | ✓    | ✓    | ✓     |
| `isEmbedded()`                        | `bool`               | ✓    | ✓    | ✓     |
| `isLinkLocal()`                       | `bool`               | ✓    | ✓    | ✓     |
| `isLoopback()`                        | `bool`               | ✓    | ✓    | ✓     |
| `isMulticast()`                       | `bool`               | ✓    | ✓    | ✓     |
| `isPrivateUse()`                      | `bool`               | ✓    | ✓    | ✓     |
| `isUnspecified()`                     | `bool`               | ✓    | ✓    | ✓     |
| `getDotAddress()`                     | `string`             | ✓    |      | ✓     |
| `getCompactedAddress()`               | `string`             |      | ✓    | ✓     |
| `getExpandedAddress()`                | `string`             |      | ✓    | ✓     |
| `getCompactedAddress()`               | `string`             |      | ✓    | ✓     |
| `getProtocolAppropriateAddress()`     | `string`             |      |      | ✓     |
