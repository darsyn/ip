# Embedding Strategies

When using version 4 and version 6 addresses interchangeably (via the
`Multi` class), version 4 addresses are *embedded* into version 6 addresses so
that both versions are stored as 16-byte binary sequences.

Unfortunately there are several different strategies for embedding a version 4
address into version 6, so this library offers various strategy implementations
for the main four:

> In the formats below, `X` marks the embedded version 4 address, and `?` marks
> bits that play no part in detection/extraction.

| Strategy Name   | Implementation                  | Format                                    |
|-----------------|---------------------------------|-------------------------------------------|
| 6to4-derived    | `Darsyn\IP\Strategy\Derived`    | `2002:XXXX:XXXX:????:????:????:????:????` |
| IPv4-compatible | `Darsyn\IP\Strategy\Compatible` | `0000:0000:0000:0000:0000:0000:XXXX:XXXX` |
| IPv4-mapped     | `Darsyn\IP\Strategy\Mapped`     | `0000:0000:0000:0000:0000:ffff:XXXX:XXXX` |
| NAT64           | `Darsyn\IP\Strategy\Nat64`      | (see below)                               |

Each embedding strategy implements the
`Darsyn\IP\Strategy\EmbeddingStrategyInterface` which defines methods to:

- Detect whether a version 4 address is embedded into a version 6 address,
- Extracting a version 4 address from a version 6 address, and
- Packing a version 4 address into a version 6 address according to the given
  strategy.

## Specifying a Strategy

> This library will automatically use the **IPv4-mapped** embedding strategy
> unless otherwise instructed.

An embedding strategy can be specified globally or on a per-instance basis.

```php
<?php
use Darsyn\IP\Strategy;
use Darsyn\IP\Version\Multi as IP;

// Set the IPv4-compatible embedding strategy to be used globally.
IP::setDefaultEmbeddingStrategy(new Strategy\Compatible);

// But for this specific instance use the 6to4-derived embedding strategy.
$ip = IP::factory('127.0.0.1', new Strategy\Derived);
```

## NAT64 (RFC 6052)
Unlike the other strategies, `Nat64` has no public constructor — it is
instantiated via named constructors only:
- `Nat64::wellKnown()` embeds at the Well-known Prefix `64:ff9b::/96`
  (RFC 6052 § 2.1). Note that RFC 6052 § 3.1 forbids embedding non-global
  version 4 addresses within the Well-known Prefix; this library deliberately
  does not enforce that restriction.
- `Nat64::localUse()` embeds at the local-use prefix `64:ff9b:1::/48` (RFC
  8215), applying RFC 6052 § 2.2 `/48` positioning; RFC 8215 defines only the
  prefix, not an embedding layout. Narrower local-use prefixes can be
  constructed through `networkSpecific()`.
- `Nat64::networkSpecific(IPv6 $prefix, int $length)` embeds at a
  Network-Specific Prefix from an operator's own unicast space. The length
  must be one of `Nat64::PREFIX_LENGTHS` (32, 40, 48, 56, 64, or 96 bits, as
  permitted by RFC 6052 § 2.2). Any bits set after that length will be zeroed.

### Well-known Prefix

| Strategy Name      | Format                                    |
|--------------------|-------------------------------------------|
| NAT64 (Well-known) | `0064:ff9b:0000:0000:0000:0000:XXXX:XXXX` |

### Local-use Prefix
The embedded IPv4 address uses the RFC 6052 § 2.2 `/48` layout; RFC 8215 defines
only the prefix.

| Strategy Name     | Format                                    |
|-------------------|-------------------------------------------|
| NAT64 (Local-use) | `0064:ff9b:0001:XXXX:??XX:XX??:????:????` |

### Network-specific Prefix
The position of the embedded IPv4 address depends on the prefix length (RFC 6052
§ 2.2).

> In the formats below, `P` marks the configured prefix

| CIDR Length | Format                                    |
|-------------|-------------------------------------------|
| `/32`       | `PPPP:PPPP:XXXX:XXXX:????:????:????:????` |
| `/40`       | `PPPP:PPPP:PPXX:XXXX:??XX:????:????:????` |
| `/48`       | `PPPP:PPPP:PPPP:XXXX:??XX:XX??:????:????` |
| `/56`       | `PPPP:PPPP:PPPP:PPXX:??XX:XXXX:????:????` |
| `/64`       | `PPPP:PPPP:PPPP:PPPP:??XX:XXXX:XX??:????` |
| `/96`       | `PPPP:PPPP:PPPP:PPPP:PPPP:PPPP:XXXX:XXXX` |
