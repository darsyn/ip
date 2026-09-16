# Doctrine Support

> This documentation is for the package `darsyn/ip-doctrine`, which was split
> into a separate package in v5+ so that PHP version requirements could be
> updated independently. Require that as a Composer dependency to use this
> functionality.

This library can be used to support IP address as column types with Doctrine
DBAL. Version `5.*` of `darsyn/ip-doctrine` supports DBAL `^2.3 || ^3.0` (PHP
`5.6` and greater), and version `6.*` supports DBAL `^4` (PHP `8.1` and
greater).

Three Doctrine types are provided to match the three version classes:

- `Darsyn\IP\Doctrine\IPV4Type` supports the `IPv4` class.
- `Darsyn\IP\Doctrine\IPV6Type` supports the `IPv6` class.
- `Darsyn\IP\Doctrine\MultiType` supports the `Multi` class.

```php
<?php
use Darsyn\IP\Doctrine\MultiType;
use Doctrine\DBAL\Types\Type;

Type::addType('ip', MultiType::class);
```

If you are using [Symfony](http://symfony.com), then add the following to your main configuration:

```yaml
doctrine:
    dbal:
        types:
            ip: Darsyn\IP\Doctrine\MultiType
```

Now you can happily store IP addresses in your entities like nobody's business:

```php
<?php
use Darsyn\IP\Version\Multi as IP;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class AnalyticsEntity
{
     #[ORM\Column(type: 'ip')]
    public IP $ipAddress;
}
```

## Querying

Doctrine converts a value through the `ip` type only when it knows the column
type. Repository methods such as `findBy()`, `findOneBy()` and the magic
`findByIpAddress()` read the type from the entity mapping, so they accept an IP
object directly.

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::fromProtocol('192.168.0.1');
$entities = $repository->findBy(['ipAddress' => $ip]);
```

The QueryBuilder and DQL do not know which column a parameter is compared
against. A parameter passed to `setParameter()` without a type is bound as a
plain string:

- An IP object is cast to its protocol notation (`"192.168.0.1"`) and compared
  against the raw bytes stored in the column. No row matches and no error is
  raised.
- A raw binary string from `getBinary()` is bound as text. This matches on MySQL
  but not on SQLite, where text and binary values never compare equal.

Always pass the type name as the third argument to `setParameter()`:

```php
<?php
use Darsyn\IP\Version\Multi as IP;

$ip = IP::fromProtocol('192.168.0.1');
$entities = $repository->createQueryBuilder('a')
    ->andWhere('a.ipAddress = :address')
    ->setParameter('address', $ip, 'ip')
    ->getQuery()
    ->getResult();
```

`'ip'` is the name the type was registered under (either `Type::addType()` or
the Symfony configuration shown above). The type accepts an IP object or a
protocol string, converts it to the stored binary form, and binds it as binary
on every database platform. If you must bind raw bytes yourself, pass
`Doctrine\DBAL\ParameterType::BINARY` (DBAL v2.8+, otherwise `\PDO::PARAM_LOB`)
as the third argument instead.
