# Doctrine Support

This library can be used to support IP address as column types with Doctrine
DBAL versions `^2.3 || ^3.0`.

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
