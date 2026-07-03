# API Reference

| Contract                     | Method                                            | Returns                        | IPv4 | IPv6 | Multi |
|------------------------------|---------------------------------------------------|--------------------------------|------|------|-------|
| `IpInterface`                | `factory(string $ip, [$strategy])` _(deprecated)_ | Static `IpInterface`           | ✓    | ✓    | ✓     |
| ↳                            | `isPublicUse()` _(deprecated)_                    | `bool`                         | ✓    | ✓    | ✓     |
| `FactoryInterface`           | `fromProtocol(string $ip, [$strategy])`           | Static `IpInterface`           | ✓    | ✓    | ✓     |
| ↳                            | `tryFromProtocol(string $ip, [$strategy])`        | Static `IpInterface` or `null` | ✓    | ✓    | ✓     |
| ↳                            | `fromBinary(string $binary, [$strategy])`         | Static `IpInterface`           | ✓    | ✓    | ✓     |
| ↳                            | `tryFromBinary(string $binary, [$strategy])`      | Static `IpInterface` or `null` | ✓    | ✓    | ✓     |
| ↳                            | `fromHex(string $hex, [$strategy])`               | Static `IpInterface`           | ✓    | ✓    | ✓     |
| ↳                            | `tryFromHex(string $hex, [$strategy])`            | Static `IpInterface` or `null` | ✓    | ✓    | ✓     |
| ↳                            | `fromIntegerString(string $integer, [$strategy])` | Static `IpInterface`           | ✓    | ✓    | ✓     |
| ↳                            | `isValid(string $ip, [$strategy])`                | `bool`                         | ✓    | ✓    | ✓     |
| `Factory4Interface`          | `fromInteger(int $integer, [$strategy])`          | Static `IpInterface`           | ✓    |      | ✓     |
| `OutputInterface`            | `getBinary()`                                     | `string`                       | ✓    | ✓    | ✓     |
| ↳                            | `getOctets()`                                     | `list<int>`                    | ✓    | ✓    | ✓     |
| ↳                            | `toIntegerString()`                               | `string`                       | ✓    | ✓    | ✓     |
| ↳                            | `toHexString()`                                   | `string`                       | ✓    | ✓    | ✓     |
| ↳                            | `toString()`                                      | `string`                       | ✓    | ✓    | ✓     |
| ↳                            | `__toString()`                                    | `string`                       | ✓    | ✓    | ✓     |
| ↳ (`JsonSerializable`)       | `jsonSerialize()`                                 | `string`                       | ✓    | ✓    | ✓     |
| `Output4Interface`           | `toDotAddress([$strategy])`                       | `string`                       | ✓    |      | ✓     |
| ↳                            | `toInteger()`                                     | `int`                          | ✓    |      | ✓     |
| `Output6Interface`           | `toCompactedAddress([$strategy])`                 | `string`                       |      | ✓    | ✓     |
| ↳                            | `toExpandedAddress()`                             | `string`                       |      | ✓    | ✓     |
| ↳                            | `getSegments()`                                   | `list<int>`                    |      | ✓    | ✓     |
| `VersionIdentityInterface`   | `getVersion()`                                    | `int`                          | ✓    | ✓    | ✓     |
| ↳                            | `isVersion(int $version)`                         | `bool`                         | ✓    | ✓    | ✓     |
| ↳                            | `isVersion4()`                                    | `bool`                         | ✓    | ✓    | ✓     |
| ↳                            | `isVersion6()`                                    | `bool`                         | ✓    | ✓    | ✓     |
| `ComparisonInterface`        | `inRange(IpInterface $ip, int $cidr)`             | `bool`                         | ✓    | ✓    | ✓     |
| ↳                            | `getCommonCidr(IpInterface $ip)`                  | `int`                          | ✓    | ✓    | ✓     |
| ↳                            | `equals(IpInterface $ip)`                         | `bool`                         | ✓    | ✓    | ✓     |
| `StrategyDetectionInterface` | `isEmbeddedAccordingToStrategy($strategy)`        | `bool`                         |      | ✓    | ✓     |
| ↳                            | `getEmbeddedIp([$strategy])`                      | `IPv4`                         |      | ✓    | ✓     |
| ↳                            | `isMapped()`                                      | `bool`                         |      | ✓    | ✓     |
| ↳                            | `isDerived()`                                     | `bool`                         |      | ✓    | ✓     |
| ↳                            | `isCompatible()`                                  | `bool`                         |      | ✓    | ✓     |
| ↳                            | `isNat64WellKnown()`                              | `bool`                         |      | ✓    | ✓     |
| ↳                            | `isNat64LocalUse()`                               | `bool`                         |      | ✓    | ✓     |
| ↳                            | `isTeredo()`                                      | `bool`                         |      | ✓    | ✓     |
| `ClassificationInterface`    | `isLinkLocal()`                                   | `bool`                         | ✓    | ✓    | ✓     |
| ↳                            | `isLoopback()`                                    | `bool`                         | ✓    | ✓    | ✓     |
| ↳                            | `isMulticast()`                                   | `bool`                         | ✓    | ✓    | ✓     |
| ↳                            | `isPrivateUse()`                                  | `bool`                         | ✓    | ✓    | ✓     |
| ↳                            | `isUnspecified()`                                 | `bool`                         | ✓    | ✓    | ✓     |
| ↳                            | `isBenchmarking()`                                | `bool`                         | ✓    | ✓    | ✓     |
| ↳                            | `isDocumentation()`                               | `bool`                         | ✓    | ✓    | ✓     |
| ↳                            | `isGloballyReachable()`                           | `bool`                         | ✓    | ✓    | ✓     |
| `Classification4Interface`   | `isBroadcast()`                                   | `bool`                         | ✓    |      | ✓     |
| ↳                            | `isShared()`                                      | `bool`                         | ✓    |      | ✓     |
| ↳                            | `isFutureReserved()`                              | `bool`                         | ✓    |      | ✓     |
| `Classification6Interface`   | `getMulticastScope()`                             | `?int`                         |      | ✓    | ✓     |
| ↳                            | `isUniqueLocal()`                                 | `bool`                         |      | ✓    | ✓     |
| ↳                            | `isUnicast()`                                     | `bool`                         |      | ✓    | ✓     |
| ↳                            | `isUnicastGlobal()`                               | `bool`                         |      | ✓    | ✓     |
| `ArithmeticInterface`        | `getNetworkIp(int $cidr)`                         | Static `IpInterface`           | ✓    | ✓    | ✓     |
| ↳                            | `getBroadcastIp(int $cidr)`                       | Static `IpInterface`           | ✓    | ✓    | ✓     |
| ↳                            | `next()`                                          | Static `IpInterface`           | ✓    | ✓    | ✓     |
| ↳                            | `previous()`                                      | Static `IpInterface`           | ✓    | ✓    | ✓     |
| ↳                            | `offset(int $offset)`                             | Static `IpInterface`           | ✓    | ✓    | ✓     |
| `MultiVersionInterface`      | `setDefaultEmbeddingStrategy($strategy)`          | `void`                         |      |      | ✓     |
| ↳                            | `toProtocolAppropriateAddress([$strategy])`       | `string`                       |      |      | ✓     |
| ↳                            | `isEmbedded()`                                    | `bool`                         |      |      | ✓     |
