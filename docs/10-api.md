## API Reference

| Method                                | Returns               | IPv4      | IPv6      | Multi    |
|---------------------------------------|-----------------------|-----------|-----------|----------|
| `factory(string $ip, [$strategy])`    | Static `IpInterface`  | &#10003;  | &#10003;  | &#10003; |
| `getBinary()`                         | `string`              | &#10003;  | &#10003;  | &#10003; |
| `getVersion()`                        | `int`                 | &#10003;  | &#10003;  | &#10003; |
| `isVersion(int $version)`             | `bool`                | &#10003;  | &#10003;  | &#10003; |
| `isVersion4()`                        | `bool`                | &#10003;  | &#10003;  | &#10003; |
| `isVersion6()`                        | `bool`                | &#10003;  | &#10003;  | &#10003; |
| `getNetworkIp(int $cidr): static`     | Static `IpInterface`  | &#10003;  | &#10003;  | &#10003; |
| `getBroadcastIp(int $cidr): static`   | Static `IpInterface`  | &#10003;  | &#10003;  | &#10003; |
| `inRange(IpInterface $ip, int $cidr)` | `bool`                | &#10003;  | &#10003;  | &#10003; |
| `isMapped()`                          | `bool`                | &#10003;  | &#10003;  | &#10003; |
| `isDerived()`                         | `bool`                | &#10003;  | &#10003;  | &#10003; |
| `isCompatible()`                      | `bool`                | &#10003;  | &#10003;  | &#10003; |
| `isEmbedded()`                        | `bool`                | &#10003;  | &#10003;  | &#10003; |
| `isLinkLocal()`                       | `bool`                | &#10003;  | &#10003;  | &#10003; |
| `isLoopback()`                        | `bool`                | &#10003;  | &#10003;  | &#10003; |
| `isMulticast()`                       | `bool`                | &#10003;  | &#10003;  | &#10003; |
| `isPrivateUse()`                      | `bool`                | &#10003;  | &#10003;  | &#10003; |
| `isUnspecified()`                     | `bool`                | &#10003;  | &#10003;  | &#10003; |
| `getDotAddress()`                     | `string`              | &#10003;  |           | &#10003; |
| `getCompactedAddress()`               | `string`              |           | &#10003;  | &#10003; |
| `getExpandedAddress()`                | `string`              |           | &#10003;  | &#10003; |
| `getCompactedAddress()`               | `string`              |           | &#10003;  | &#10003; |
| `getProtocolAppropriateAddress()`     | `string`              |           |           | &#10003; |
