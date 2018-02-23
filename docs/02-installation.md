## Installation

### System Requirements

- This library will not support end-of-life PHP versions, therefore only PHP
  5.6+ is supported. You can expect support for 7.0+ only commencing 1st January
  2019.
- This library cannot be used on 32-bits systems due to a dependency on the
  in-built PHP functions `inet_pton` and `inet_ntop`. This dependency may be
  circumvented in a future version but is not guaranteed.

### Install

The library is available on [Packagist](https://packagist.org/packages/darsyn/ip)
and should be installed using [Composer](https://getcomposer.org/). This can be 
done by running the following command on a composer installed box:

```bash
$ composer require darsyn/ip
```

Most modern frameworks will include Composer out of the box, but ensure the
following file is included:

```php
<?php declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';
```
