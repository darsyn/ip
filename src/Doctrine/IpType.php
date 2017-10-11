<?php

namespace Darsyn\IP\Doctrine;

use Darsyn\IP\Exception\InvalidIpAddressException;
use Darsyn\IP\IP;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

/**
 * Field type mapping for the Doctrine Database Abstraction Layer (DBAL).
 *
 * UUID fields will be stored as a string in the database and converted back to
 * the Uuid value object when querying.
 */
class IpType extends Type
{
    /**
     * @var string
     */
    const NAME = 'ip';

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $fieldDeclaration['length'] = 16;
        return $platform->getBinaryTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof IP) {
            return $value;
        }

        // PostgreSQL will return the binary data as a resource instead of a
        // string (like MySQL).
        if (is_resource($value) && get_resource_type($value) === 'stream') {
            $value = stream_get_contents($value);
        }
        if (empty($value)) {
            return null;
        }

        try {
            $ip = new IP($value);
        } catch (InvalidIpAddressException $e) {
            throw ConversionException::conversionFailed($value, self::NAME);
        }
        return $ip;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        try {
            $ip = $value instanceof IP ? $value : new IP($value);
            return $ip->getBinary();
        } catch (InvalidIpAddressException $e) {
            throw ConversionException::conversionFailed($value, static::NAME);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getBindingType()
    {
        return \PDO::PARAM_LOB;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
