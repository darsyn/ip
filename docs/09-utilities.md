# Utilities

## Binary Utility

The IP value objects store their internal state as a binary string, which is not
easy for humans to understand. The `Darsyn\IP\Binary` class is a collection of
static helper methods for dealing with such binary strings.

### `fromHex()`

> `\Darsyn\IP\Binary::fromHex(string $hex): string`

Because there are two hexadecimal characters per byte, the input string must be
of a length that is a multiple of 2.

```php
<?php
use Darsyn\IP\Binary;

$hexString = '48656c6c6f21';
Binary::fromHex($hexString); // string("Hello!")
```

### `toHex()`

> `\Darsyn\IP\Binary::toHex(string $hex): string`

```php
<?php
use Darsyn\IP\Binary;

$binaryString = 'Hello!';
Binary::toHex($binaryString); // string("48656c6c6f21")
```

### `fromHumanReadable()`

> `\Darsyn\IP\Binary::fromHumanReadable(string $asciiBinarySequence): string`

Because there are 8 bits per bytes, the input string must be of a length that is
a multiple of 8.

```php
<?php
use Darsyn\IP\Binary;

$asciiBinary = '010010000110010101101100011011000110111100100001';
Binary::fromHumanReadable($asciiBinary); // string("Hello!")
```

### `toHumanReadable()`

> `\Darsyn\IP\Binary::toHumanReadable(string $binary): string`

```php
<?php
use Darsyn\IP\Binary;

$binaryString = 'Hello!';
Binary::toHumanReadable($asciiBinary); // string("010010000110010101101100011011000110111100100001")
```

### Others

On some PHP installations, the [Multibyte String](https://www.php.net/manual/en/book.mbstring.php)
extension can overload PHP's native string functions which, because we are working
with arbitrary binary content, can incorrectly guess an encoding when attempting
to transform this data (IP addresses have no text encoding).

Because of this, the Binary helper class also has some static methods to be used
in place of PHP's string functions which detect if the appropriate functions from
the Multibyte String extension exist and then explicitly states that we are
working with `8bit` byte data as the text encoding.

| Static Helper Method  | Replaces the PHP function... |
|-----------------------|------------------------------|
| `Binary::getLength()` | `str_len()`                  |
| `Binary::subString()` | `substr()`                   |
| `Binary::padString()` | `str_pad()`                  |
