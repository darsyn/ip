# Darsyn IP

## `5.0.0`

- Removed Doctrine functionality, and split it off into its own package:
  [`darsyn/ip-doctrine`](https://packagist.org/packages/darsyn/ip-doctrine).
  List it as a Composer dependency suggestion.
- Change from [Psalm](https://psalm.dev/) to [PHPStan](https://phpstan.org/) for
  static analysis.
  - Add types to all function arguments lists and return values.
  - Update the codebase to pass static analysis on `max` level (standard,
    deprecation, and bleeding edge rules).
- Test against PHP versions `8.2` and `8.3` in CI pipeline.
- Update README with notes on version compatibility.
- Explicitly state the requirement of the `ctype` PHP extension.
- Add PHPUnit attributes alongside annotations to be compatible with the highest
  version of PHPUnit for any supported PHP version.

## `4.1.0`

- Added `IpInterface::equals()` method for comparing two IP addresses.
- Added `getCommonCidr(IpInterface $ip): int` for determining how in range two
  IP addresses are according to their common CIDR value.
- Added `isBenchmarking()`, `isDocumentation()`, and `isPublicUse()` type
  methods for both IPv4 and IPv6 addresses.
- Added `isBroadcast()`, `isShared()`, and `isFutureReserved()` type methods for
  IPv4 addresses.
- Added `getMulticastScope()`, `isUniqueLocal()`, `isUnicast()`, and
  `isUnicastGlobal()` type methods for IPv6 addresses.
- Added `Ipv6::fromEmbedded()` factory method to create an instance of an
  IPv4-embedded address as IPv6 instead of Multi.
- Made internal helper methods for dealing with binary data into utility
  classes: `Darsyn\IP\Util\Binary` and `Darsyn\IP\Util\MbString`.
- Complete documentation overhaul
- Increase test coverage.
- Started using static analysis both locally and via GitHub actions.
- Documentation and tests are excluded from the Git archive to reduce download
  size when installing Composer dependency as dist.
- Updated Code of Conduct to Contributor Covenant v2.1

## `4.0.2`

- Add return types to DocComments to prevent
  [`symfony/error-handler`](https://github.com/symfony/symfony/tree/5.4/src/Symfony/Component/ErrorHandler)
  from throwing deprecation errors

## `4.0.1`

- Add Code of Conduct to project.
- Add new internal helper for dealing with binary strings.
- Add namespace indicator to function calls to speed up symbol resolution.
- Add `__toString()` to IP objects.
- Update unit tests, now runnable on all PHP versions 5.6 to 8.1

## `4.0.0`

- Complete rewrite of library.
