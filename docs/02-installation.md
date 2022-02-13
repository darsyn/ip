# Installation

## System Requirements

- All functionality can be implemented without using new language features, so
  this library will support PHP versions `5.6` onwards.
- This library cannot be used on 32-bits systems due to a dependency on the
  in-built PHP functions `inet_pton` and `inet_ntop`.

## Install

The library is available on [Packagist](https://packagist.org/packages/darsyn/ip)
and should be installed using [Composer](https://getcomposer.org/). This can be 
done by running the following command:

```bash
$ composer require darsyn/ip
```

Most modern frameworks will include Composer out of the box, but ensure the
following file is included:

```php
<?php declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';
```
