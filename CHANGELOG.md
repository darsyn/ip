# Darsyn IP

## `6.x`

- Convert IP addresses to and from integers: `fromInteger()`/`toInteger()` via
  the new `Contracts\Factory4Interface` (IPv4 and Multi only), plus
  arbitrary-precision `fromIntegerString()`/`toIntegerString()` and fixed-width
  `toHexString()` on all classes.
- Add arbitrary-precision base-256 <-> base-10 conversion to `Util\Binary`:
  `toDecimalString()` and `fromDecimalString()` (GMP fast path when the
  extension is loaded, backed by pure-PHP fallback).
- Extend `Contracts\OutputInterface` from `\JsonSerializable`.
- Expand `Contracts\OutputInterface`: `getOctets()`, `getSegments()`, and
  canonical `toString()` (deferable method for Stringable equivalent).
- Add address arithmetic methods `next()`, `previous()` and `offset(int $offset)`
  to `Contracts\ArithmeticInterface`. Over/underflowing the address space throws
  `Exception\OverflowException`.
- Split embedding strategies into canonical and non-canonical packers via the
  new `Strategy\CanonicalEmbeddingInterface` bridge.
- Add strict parsing methods to the new `Contracts\FactoryInterface` interface:
  try/from protocol, binary, hex.
- Deprecate `factory()` in favour of the strict named constructors
  `fromProtocol()`/`fromBinary()`.
- Introduce `@experimental` capability interfaces under `Darsyn\IP\Contracts\`.
  Their shape may change before `7.0`, but remains backwards compatible for `6.x`
- Allow overriding the global formatter per call by passing a
  `Formatter\ProtocolFormatterInterface` as the first argument to
  `IPv4::getDotAddress()`, `IPv6::getCompactedAddress()`,
  `Multi::getDotAddress()` and `Multi::getProtocolAppropriateAddress()`.
- Move CIDR mask generation from the protected `AbstractIP::generateBinaryMask()`
  to the public static `Util\Binary::mask()`.
- Performance: compute network masks (`getNetworkIp()` / `getBroadcastIp()`)
  and the greatest common CIDR (`getCommonCidr()`) directly on raw bytes.
- Add arithmetic primitives to `Util\Binary`: `increment()`, `decrement()` and
  `addIntegerOffset()`. Over/underflowing throws new exception.
- Widen the `$previous` constructor argument on all exception classes from
  `?\Exception` to `?\Throwable`.
- Add support for PHP `8.5` in README, and GitHub Action CI workflows.
- Access protected method via Closure in unit tests, instead of reflection, to
  fix deprecation warnings in PHP `8.5`.
- Define all data-provider arguments as test method arguments, to fix
  deprecation warnings in PHPUnit (cherry-picked from `5.0.2`).
- Drop all PHP versions except floor (`7.4`) and latest (`8.5`) for running
  static analysis.
- Type-narrow out-of-range CIDRs to remove unnecessary `@phpstan-ignore`
  instructions.
- Bugfix: don't compress single 16-bit zero group (according to RFC 5952 § `4.2.2`).
- Bugfix: stop `MbString::subString()` from swallowing valid `0`.
- Add Bash script for testing GitHub Action workflows locally using Docker.
- Add code style ruleset definition via PHP-CS-Fixer configuration. Apply the
  code style to the entire codebase, and enable checking as another CI job in
  GitHub Actions.
- Bugfix: classify full `fc00::/7` block as private use.
- Add (or update) community standards: `SECURITY.md`, `CODE_OF_CONDUCT.md` and
  `CONTRIBUTING.md`
- Bugfix: detect embedded IPv4 across the entire 6to4 block (`2002::/16`, RFC
  3056 § 2), not just canonical 6to4 addresses with a zeroed 80-bit tail.
- Bugfix: align `isPublicUse()`, `isUnicastGlobal()` and `isDocumentation()`
  with the IANA special-purpose address registries, and update RFC
  references/citations for all address classification methods.
- Rename `isPublicUse()` to `isGloballyReachable()`, conforming to the official
  wording used in the IANA special-purpose address registries ("Public Use"
  does not appear in them). Keep `isPublicUse()` as a deprecated alias.
- Add NAT64 (RFC 6052 § 2.2) embedding strategy, with named constructors for
  the Well-known Prefix (`64:ff9b::/96`), operator Network-specific Prefixes,
  and the RFC 8215 Local-use prefix (`64:ff9b:1::/48`).
- Add `Teredo` embedding strategy for `2001::/32` (according to RFC 4380 § 4).
- Add `Composite` embedding strategy that recognises and extracts version 4
  addresses across several underlying strategies, while packing through a single
  canonical strategy.
- Bugfix: classify a NAT64 Well-known Prefix (`64:ff9b::/96`, RFC 6052 § 2.1)
  address by the version 4 address it embeds rather than trusting the prefix, so
  an embedded non-globally-reachable address is no longer reported as globally
  reachable (closes a potential SSRF deny-list bypass).

## `6.0.0`

- Rename default branch name from `develop`/`master` to `6.x`; branch `5.x` from
  `5.0.0` tag for ongoing support (bugfixes only). Update GitHub Actions CI
  workflows, and update README with description on the `6.x`/`5.x` split.
- Drop support for PHP versions less than `7.1`.
- Fix PHP `8.4` deprecation errors (nullable arguments must be explicit).
- Add strict types.
- Add native type support. Remove unnecessary type annotations in doc blocks.
  Remove unit tests that assert invalid types no longer allowed by PHP's strict
  typing.

## `5.0.2`

> `5.0.1` added PHP `8.4` support, but this is deprecated due to true support
> (without supresing deprecation warnings) being unavailable without lifting
> the minimum PHP version (`5.6`).
>
> PHP `8.4` is now only supported (officially) on `6.x`.

- Officially deprecate PHP `8.4` support for the `5.x` branch. Highest PHP
  version supported for `5.x` is `8.3`, upgrade to `6.x` for higher PHP versions.
- Update version requirements in README, `composer.json` and GitHub Action CI
  workflows.
- Define all data-provider arguments as test method arguments, to fix
  deprecation warnings in PHPUnit.
- Remove `@phpstan-ignore` instruction for non-issue in unit tests.

## `5.0.1`

- Add support for PHP `8.4`.
- Reference comment added for generating documentation on Podman.

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
