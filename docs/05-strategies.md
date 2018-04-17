## Embedding Strategies

When using version 4 and version 6 addresses interchangeably (via the 
[`Multi`](../src/Version/Multi.php) class), version 4 addresses are *embedded*
into version 6 addresses so that both versions are stored as 16-byte binary
sequences.

Unfortunately there are several different strategies for embedding a version 4
address into version 6, so this library offers various strategy implementations
for the main three: 

| Strategy Name   | Implementation                                                    | Format                                    |
|-----------------|-------------------------------------------------------------------|-------------------------------------------|
| 6to4-derived    | [`Darsyn\IP\Strategy\Derived`](../src/Strategy/Derived.php)       | `2002:XXXX:XXXX:0000:0000:0000:0000:0000` |
| IPv4-compatible | [`Darsyn\IP\Strategy\Compatible`](../src/Strategy/Compatible.php) | `0000:0000:0000:0000:0000:0000:XXXX:XXXX` |
| IPv4-mapped     | [`Darsyn\IP\Strategy\Mapped`](../src/Strategy/Mapped.php)         | `0000:0000:0000:0000:0000:ffff:XXXX:XXXX` |

Each embedding strategy implements the
[`EmbeddingStrategyInterface`](../src/Strategy/EmbeddingStrategyInterface.php)
which defines methods to: detect whether a version 4 address is embedded into a
version 6 address; extracting a version 4 address from a version 6 address; and
packing a version 4 address into a version 6 address according to the given strategy.

### Specifying a Strategy

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
