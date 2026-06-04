# Embedding Strategies

When using version 4 and version 6 addresses interchangeably (via the
`Multi` class), version 4 addresses are *embedded* into version 6 addresses so
that both versions are stored as 16-byte binary sequences.

Unfortunately there are several different strategies for embedding a version 4
address into version 6, so this library offers various strategy implementations
for the main four (and one deprecated):

| Strategy Name   | Implementation                  | Format                                    | RFC                                                                    | Notes                 |
|-----------------|---------------------------------|-------------------------------------------|------------------------------------------------------------------------|-----------------------|
| IPv4-mapped     | `Darsyn\IP\Strategy\Mapped`     | `0000:0000:0000:0000:0000:ffff:XXXX:XXXX` | [RFC 4291 § 2.5.5.2](https://tools.ietf.org/html/rfc4291)              | Default               |
| NAT64           | `Darsyn\IP\Strategy\Nat64`      | `0064:ff9b:0000:0000:0000:0000:XXXX:XXXX` | [RFC 6052 § 2.1](https://tools.ietf.org/html/rfc6052)                  | Translator            |
| 6to4-derived    | `Darsyn\IP\Strategy\Derived`    | `2002:XXXX:XXXX:0000:0000:0000:0000:0000` | [RFC 3056](https://tools.ietf.org/html/rfc3056)                        | Relay                 |
| Teredo          | `Darsyn\IP\Strategy\Teredo`     | `2001:0000:xxxx:xxxx:xxxx:xxxx:XXXX:XXXX` | [RFC 4380 § 4](https://tools.ietf.org/html/rfc4380)                    | Tunnel (extract-only) |
| IPv4-compatible | `Darsyn\IP\Strategy\Compatible` | `0000:0000:0000:0000:0000:0000:XXXX:XXXX` | [RFC 4291 § 2.5.5.1](https://tools.ietf.org/html/rfc4291)              | Deprecated            |

> The 6to4-derived strategy detects only the canonical 6to4 network address
> (zero SLA and interface identifier bits, as shown in the format column);
> host addresses within a 6to4 `/48` are intentionally not treated as embedded.
> The Teredo strategy stores the client's public IPv4 address XOR'd with
> `0xFFFFFFFF` in the last four bytes (its flags field was later redefined by
> [RFC 5991](https://tools.ietf.org/html/rfc5991), which does not affect
> extraction).

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

## Composite

The `Composite` strategy accepts one or more embedding strategies to verify
address embedding against. But only the first strategy supplied is used to embed
IPv4 into IPv6 (the "packer").

```php
<?php
use Darsyn\IP\Strategy\Composite;
use Darsyn\IP\Strategy\Mapped;
use Darsyn\IP\Strategy\Nat64;
use Darsyn\IP\Version\Multi as IP;

// Recognise both IPv4-mapped and NAT64 embeddings; pack as IPv4-mapped.
$strategy = new Composite(new Mapped, new Nat64);

// Both Mapped and NAT64 embedded addresses are recognised as IPv4.
$mapped = IP::factory('::ffff:7f00:1', $strategy);
$mapped->getDotAddress(); // string("127.0.0.1")
$nat64 = IP::factory('64:ff9b::7f00:1', $strategy);
$nat64->getDotAddress(); // string("127.0.0.1")

// But only the first argument (in this example, Mapped) is used to pack an
// IPv4 address into IPv6.
IP::factory('127.0.0.1', $strategy)->getCompactedAddress(); // string("::ffff:7f00:1")
// Existing IPv6 addresses retain their scheme and don't get "re-packed" into
// the first strategy.
IP::factory('64:ff9b::7f00:1', $strategy)->getCompactedAddress(); // string("64:ff9b::7f00:1")
```

### Named Constructor

`Composite::all()` returns a composite of every **unambiguous, non-deprecated**
strategy:
- IPv4-mapped (`::ffff:0:0/96`) as the packer,
- 6to4 (`2002::/16`),
- the NAT64 Well-Known Prefix (`64:ff9b::/96`), and
- Teredo (`2001::/32`).

```php
<?php
use Darsyn\IP\Strategy\Composite;
use Darsyn\IP\Version\Multi as IP;

// Equivalent to: new Composite(new Mapped, new Derived, new Nat64, new Teredo).
$strategy = Composite::all();

// An address embedded under any of the four schemes is recognised and
// resolves to the same version 4 address.
IP::factory('::ffff:7f00:1', $strategy)->getProtocolAppropriateAddress();   // string("127.0.0.1")
IP::factory('2002:7f00:1::', $strategy)->getProtocolAppropriateAddress();   // string("127.0.0.1")
IP::factory('64:ff9b::7f00:1', $strategy)->getProtocolAppropriateAddress(); // string("127.0.0.1")
IP::factory('2001:0:4136:e378:8000:63bf:80ff:fffe', $strategy)->getProtocolAppropriateAddress(); // string("127.0.0.1")
```

> **Note:** `all()` deliberately **excludes** the ambiguous, deprecated
> IPv4-compatible (`Compatible`, `::/96`) strategy.
