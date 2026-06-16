# Embedding Strategies

When using version 4 and version 6 addresses interchangeably (via the
`Multi` class), version 4 addresses are *embedded* into version 6 addresses so
that both versions are stored as 16-byte binary sequences.

Unfortunately there are several different strategies for embedding a version 4
address into version 6, so this library offers various strategy implementations:

> In the formats below, `X` marks the embedded version 4 address, and `?` marks
> bits that play no part in detection/extraction.

| Strategy Name   | Implementation                  | Format                                    |
|-----------------|---------------------------------|-------------------------------------------|
| IPv4-mapped     | `Darsyn\IP\Strategy\Mapped`     | `0000:0000:0000:0000:0000:ffff:XXXX:XXXX` |
| 6to4-derived    | `Darsyn\IP\Strategy\Derived`    | `2002:XXXX:XXXX:????:????:????:????:????` |
| NAT64           | `Darsyn\IP\Strategy\Nat64`      | (see below)                               |
| Teredo          | `Darsyn\IP\Strategy\Teredo`     | `2001:0000:????:????:????:????:XXXX:XXXX` |
| IPv4-compatible | `Darsyn\IP\Strategy\Compatible` | `0000:0000:0000:0000:0000:0000:XXXX:XXXX` |

Each embedding strategy implements the
`Darsyn\IP\Strategy\EmbeddingStrategyInterface` which defines methods to:

- Detect whether a version 4 address is embedded into a version 6 address,
- Extracting a version 4 address from a version 6 address, and
- Packing a version 4 address into a version 6 address according to the given
  strategy (see [Canonical and Non-Canonical Packing](#canonical-and-non-canonical-packing)).

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

## Canonical and Non-Canonical Packing

Some embedding strategies (6to4-derived, Teredo, and NAT64 with a prefix shorter
than `/96`) carry bits *outside* the embedded version 4 address — a Teredo
address, for example, also carries the tunnel server's address, flags, and the
client's UDP port. The original `pack()` always produces the **canonical** form,
zeroing every such bit; this is the right behaviour when constructing an address
from a bare version 4 address, but it silently discards information when re-packing
an existing version 6 address.

`Darsyn\IP\Strategy\CanonicalEmbeddingInterface` provides methods (and
deprecates `pack()`):
- `packIntoCanonical(string $ipv4): string` is identical to `pack()`: every bit
  outside the embedded version 4 address is normalised/zeroed.
- `packIntoNonCanonical(string $ipv6, string $ipv4): string` replaces only the
  embedded version 4 bit positions of `$ipv6`, preserving every other bit. A
  `PackingException` is thrown if `$ipv6` is not recognised by the strategy.

> `CanonicalEmbeddingInterface` is a _temporary scaffolding_ (a bridge interface)
> to maintain backwards compatibility for `EmbeddingStrategyInterface` on the
> `6.x` branch. Both interfaces will be combined back into `EmbeddingStrategyInterface`
> with `pack()` removed on the next major version bump.
>
> Type-hint `EmbeddingStrategyInterface` and use feature detection
> (`instanceof CanonicalEmbeddingInterface`) if you wish to use the new methods
> in custom strategies.

```php
<?php
use Darsyn\IP\Strategy\Teredo;

$strategy = new Teredo;
// A Teredo address carrying server 65.54.227.120, flags, and client port 40000.
$ipv6 = pack('H*', '200100004136e378800063bf3ffffdd2');
$ipv4 = pack('H*', '7f000001'); // 127.0.0.1

// Canonical packing keeps only the client address, zeroing server/flags/port.
bin2hex($strategy->packIntoCanonical($ipv4)); // string("20010000000000000000000080fffffe")

// Non-canonical packing embeds the new client but preserves the server, flags,
// and port carried by the original address.
bin2hex($strategy->packIntoNonCanonical($ipv6, $ipv4)); // string("200100004136e378800063bf80fffffe")
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

## Composite

The `Composite` strategy combines several embedding strategies behind a single
strategy. An address is recognised as embedded if **any** of the underlying
strategies recognises it, and extraction is delegated to the **first** strategy
(in constructor order) that recognises the address.

Packing is asymmetric: only the first strategy supplied — the *packer* — is ever
used to embed a version 4 address into version 6. A `Composite` can therefore
recognise several embedding schemes on input while always producing a single
canonical form on output.

```php
<?php
use Darsyn\IP\Strategy\Composite;
use Darsyn\IP\Strategy\Mapped;
use Darsyn\IP\Strategy\Nat64;
use Darsyn\IP\Version\Multi as IP;

// Recognise both IPv4-mapped and NAT64 (Well-known Prefix) embeddings, but
// always pack as IPv4-mapped.
$strategy = new Composite(new Mapped, Nat64::wellKnown());

// Addresses embedded under either scheme are recognised as version 4.
IP::factory('::ffff:7f00:1', $strategy)->getDotAddress();   // string("127.0.0.1")
IP::factory('64:ff9b::7f00:1', $strategy)->getDotAddress(); // string("127.0.0.1")

// But only the first strategy (here, Mapped) is ever used to pack a version 4
// address into version 6.
IP::factory('127.0.0.1', $strategy)->getCompactedAddress(); // string("::ffff:7f00:1")
```
