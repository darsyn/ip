<?php

namespace Darsyn\IP\Doctrine;

use Darsyn\IP\InvalidIpAddressException;
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
     * Get SQL Declaration
     *
     * @access public
     * @param array $fieldDeclaration
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $fieldDeclaration['length'] = 16;
        return $platform->getBinaryTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * Convert to PHP Value
     *
     * @access public
     * @param string $value
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     * @throws \Doctrine\DBAL\Types\ConversationException
     * @return \Darsyn\IP\IP
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof IP) {
            return $value;
        }

        // PostgreSQL will return the binary data as a resource instead of
        // a string (like MySQL).
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
     * Convert to Database Value
     *
     * @access public
     * @param \Darsyn\IP\IP $value
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     * @throws \Doctrine\DBAL\Types\ConversationException
     * @return void
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return;
        }
        try {
            return (string) ($value instanceof IP ? $value : new IP($value));
        } catch (InvalidIpAddressException $e) {
            throw ConversionException::conversionFailed($value, static::NAME);
        }
    }

    /**
     * Get Type Name
     *
     * @access public
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Requires SQL Comment Hint?
     *
     * @access public
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     * @return boolean
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
