## Doctrine Support

This library can be used to support IP address as column types with Doctrine.
Three Doctrine types are provided to match the three version classes:

- [`IPV4Type`](../src/Doctrine/IPv4Type.php) supports [`IPv4`](../src/Version/IPv4.php).
- [`IPV6Type`](../src/Doctrine/IPv6Type.php) supports [`IPv6`](../src/Version/IPv6.php).
- [`MultiType`](../src/Doctrine/MultiType.php) supports [`Multi`](../src/Version/Multi.php).

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

Now you can happily store IP addresses in your entites like nobody's business:

```php
<?php
use Darsyn\IP\Version\Multi as IP;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AnalyticsEntity
{
    /**
     * @ORM\Column(type="ip")
     */
    protected $ipAddress;

    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    public function setIpAddress(IP $ip)
    {
        $this->ipAddress = $ip;
    }
}
```
