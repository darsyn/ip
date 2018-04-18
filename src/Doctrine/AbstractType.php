<?php

namespace Darsyn\IP\Doctrine;

use Darsyn\IP\Exception\InvalidIpAddressException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

/**
 * Field type mapping for the Doctrine Database Abstraction Layer (DBAL).
 *
 * IP fields will be stored as a string in the database and converted back to
 * the IP value object when querying.
 */
abstract class AbstractType extends Type
{
    const NAME = 'ip';
    const IP_LENGTH = 16;

    /**
     * @return string
     */
    abstract protected function getIpClass();

    /**
     * @param string $ip
     * @throws \Darsyn\IP\Exception\InvalidIpAddressException
     * @throws \Darsyn\IP\Exception\WrongVersionException
     * @return \Darsyn\IP\IpInterface
     */
    abstract protected function createIpObject($ip);

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getBinaryTypeDeclarationSQL(['length' => static::IP_LENGTH]);
    }

    /**
     * {@inheritdoc}
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (is_a($value, $this->getIpClass())) {
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
            $ip = $this->createIpObject($value);
        } catch (InvalidIpAddressException $e) {
            throw ConversionException::conversionFailed($value, self::NAME);
        }
        return $ip;
    }

    /**
     * {@inheritdoc}
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        try {
            /** @var \Darsyn\IP\IpInterface $ip */
            $ip = is_a($value, $this->getIpClass()) ? $value : $this->createIpObject($value);
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
