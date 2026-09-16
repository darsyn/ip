# Darsyn IP

## `6.1.0`

> Adds strict parsing, integer conversion, address arithmetic and three new embedding strategies.
>
> The public API is now split into capability interfaces under `Darsyn\IP\Contracts\`. These interfaces are
> `@experimental` (their shape may change before `7.0`, but stay backwards compatible for `6.x`).
>
> Most deprecations in this release are docblock `@deprecated` only, so use a static analyzer.

### Upgrading from 6.0 (BC-breaks)

- Hand-written implementors of `IpInterface` that don't extend `AbstractIP` require new methods provided in the
  `Darsyn\IP\Contracts\` interfaces.
- Protected method `generateBinaryMask()` was removed from `AbstractIP`.
- A concrete return type was added to `AbstractIP::jsonSerialize()`. Subclasses that override this method with a
  different return type will now break.
- Subclasses of native packing strategies (`Mapped`, `Compatible`, `Derived`) that had custom implementations of
  `pack()` will now be silently bypassed. Implement `packIntoCanonical()` and `packIntoNonCanonical()`.
- String casts and JSON now go through the `to*` methods. A subclass override of `getDotAddress()` or its siblings gets
  silently bypassed and stops affecting output.
- `IPv6::fromEmbedded()` now lets a custom strategy's `PackingException` escape instead of wrapping it.
- 6to4 widening on the `Derived` strategy: embedded IPv4 addresses are now detected across the whole `2002::/16` block,
  so non-canonical 6to4 addresses are version-4 values. Canonical packing drops bits 48 to 127.
- Strings generated `<6.1` for single-zero-group addresses will no longer match new output (RFC 5952 fix).

### Parsing

- Add strict named constructors on the new `Contracts\FactoryInterface` (`fromProtocol()`, `fromBinary()` and
  `fromHex()`). Each one has a `tryFrom*` twin that returns `null` instead of throwing an exception.
- `isValid()` reports whether a string parses.
- Add `fromInteger()` and `tryFromInteger()` on the new `Contracts\Factory4Interface` (IPv4 and Multi).
- Arbitrary-precision `fromIntegerString()` and `tryFromIntegerString()` on all classes.
- Fix: `Multi::fromProtocol()` now rejects a binary string of the wrong length instead of passing it through.

### Output

- Add `toString()` as the canonical string form. `__toString()` now defers to it.
- `Contracts\OutputInterface` extends `\JsonSerializable` so an address encodes as canonical string.
- Add `getOctets()` on all classes and `getSegments()` on IPv6 and Multi.
- Add `toInteger()` (IPv4 and Multi), `toIntegerString()` and fixed-width `toHexString()` on all classes.
- Rename the whole-value output methods with prefix `to*`. `get*` stays for component and property accessors, and
  `getBinary()` (accessor of the internal state).
- Accept an optional `Formatter\ProtocolFormatterInterface` argument on `toDotAddress()`, `toCompactedAddress()` and
  `toProtocolAppropriateAddress()` to override the global formatter.

### Arithmetic

- Add `next()`, `previous()` and `offset(int $offset)` to `Contracts\ArithmeticInterface`.
- Performance: `getNetworkIp()`, `getBroadcastIp()` and `getCommonCidr()` now work on raw bytes.

### Embedding strategies

- Add the `Strategy\Nat64` strategy (RFC 6052 § 2.2) with named constructors for the Well-Known Prefix `64:ff9b::/96`,
  operator Network-Specific Prefixes, and the RFC 8215 Local-Use prefix `64:ff9b:1::/48`.
- Add the `Strategy\Teredo` strategy for `2001::/32` (RFC 4380 § 4).
- Add the `Strategy\Composite` strategy. Detects and extracts an embedded address through several strategies, and packs
  through one canonical strategy.
- Add `Contracts\StrategyDetectionInterface` on IPv6 and Multi: `isEmbeddedAccordingToStrategy()`, `isNat64WellKnown()`,
  `isNat64LocalUse()`, `isTeredo()` and `getEmbeddedIp()` (the inverse of `IPv6::fromEmbedded()`).
- Split packing into canonical and non-canonical forms through the new `Strategy\CanonicalEmbeddingInterface` bridge.

### Classification

- Rename `isPublicUse()` to `isGloballyReachable()`, the term used by IANA special-purpose address registries.
- Bugfix: align `isGloballyReachable()`, `isUnicastGlobal()` and `isDocumentation()` with the IANA special-purpose
  address registries, and update the RFC citations on all classification methods.
- Bugfix: classify a NAT64 Well-Known Prefix address by the IPv4 address it embeds, not by its prefix. An embedded
  non-reachable address is no longer reported as globally reachable (closes an SSRF deny-list bypass issue).
- Bugfix: classify the whole `fc00::/7` block as private use.

### Deprecated

- `factory()` (use `fromProtocol()` or `fromBinary()`).
- `isPublicUse()` (use `isGloballyReachable()`).
- `getDotAddress()`, `getCompactedAddress()`, `getExpandedAddress()` and `getProtocolAppropriateAddress()`.
  Use the `to*` spelling.
- `IpInterface::isEmbedded()`, `isMapped()`, `isDerived()` and `isCompatible()` (use
  `Contracts\StrategyDetectionInterface`).
- `EmbeddingStrategyInterface::pack()`. Implement `Strategy\CanonicalEmbeddingInterface` and use `packIntoCanonical()`.

### Utilities

- Add byte-string arithmetic to `Util\Binary` (`increment()`, `decrement()` and `addIntegerOffset()`).
- Add base-256 to base-10 conversion to `Util\Binary` (`toDecimalString()` and `fromDecimalString()`).
  Use GMP when the extension is loaded.
- Add `Util\Binary::mask()`.
- Add `Util\MbString::split()` (byte-safe `str_split()`).
- Bugfix: `MbString::subString()` no longer swallows a valid `"0"`.
- Widen the `$previous` argument on all exception classes from `?\Exception` to `?\Throwable`.

### Project

- Add PHP `8.5` to the supported versions and the CI matrix.
- Add a PHP-CS-Fixer ruleset.
- Add test script that uses Docker to mirror the CI workflows.
- Add `SECURITY.md`, `CODE_OF_CONDUCT.md` and `CONTRIBUTING.md`.

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
