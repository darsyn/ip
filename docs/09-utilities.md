# Utilities

## Binary Utility

The IP value objects store their internal state as a binary string, which is not
easy for humans to understand. The `Darsyn\IP\Util\Binary` class is a collection
of static helper methods for dealing with such binary strings.

### From Hexadecimal

> ```
> @throws \InvalidArgumentException
>
> \Darsyn\IP\Util\Binary::fromHex(string $hex): string
> ```

Because there are two hexadecimal characters per byte, the input string must be
of a length that is a multiple of 2.

```php
<?php
use Darsyn\IP\Util\Binary;

$hexString = '48656c6c6f21';
Binary::fromHex($hexString); // string("Hello!")
```

### To Hexadecimal

> ```
> @throws \InvalidArgumentException
>
> \Darsyn\IP\Util\Binary::toHex(string $hex): string
> ```

```php
<?php
use Darsyn\IP\Util\Binary;

$binaryString = 'Hello!';
Binary::toHex($binaryString); // string("48656c6c6f21")
```

### From Human-readable Binary

> ```
> @throws \InvalidArgumentException
>
> \Darsyn\IP\Util\Binary::fromHumanReadable(string $asciiBinarySequence): string
> ```

Because there are 8 bits per bytes, the input string must be of a length that is
a multiple of 8.

```php
<?php
use Darsyn\IP\Util\Binary;

$asciiBinary = '010010000110010101101100011011000110111100100001';
Binary::fromHumanReadable($asciiBinary); // string("Hello!")
```

### To Human-readable

> ```
> @throws \InvalidArgumentException
>
> \Darsyn\IP\Util\Binary::toHumanReadable(string $binary): string
> ```

```php
<?php
use Darsyn\IP\Util\Binary;

$binaryString = 'Hello!';
Binary::toHumanReadable($asciiBinary); // string("010010000110010101101100011011000110111100100001")
```

## Multibyte String Utility

On some PHP installations, the [Multibyte String](https://www.php.net/manual/en/book.mbstring.php)
extension can overload PHP's native string functions which, because we are working
with arbitrary binary content, can incorrectly guess an encoding when attempting
to transform this data (IP addresses have no text encoding).

Because of this, the `Darsyn\IP\Util\MbString` class is a collection of static
helper equivalents for PHP's core string functions which detect if the
`mbstring` extension is installed and appropriately call the correct function,
specifying the `8bit` text encoding (treat each byte as individual character)
if required.

| Static Helper Method    | Replaces the PHP function... |
|-------------------------|------------------------------|
| `MbString::getLength()` | `str_len()`                  |
| `MbString::subString()` | `substr()`                   |
| `MbString::padString()` | `str_pad()`                  |
