<?php declare(strict_types=1);

namespace Darsyn\IP\Doctrine;

use Darsyn\IP\Exception\InvalidIpAddressException;
use Darsyn\IP\IpInterface;
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

    abstract protected function getIpClass(): string;

    /**
     * @throws \Darsyn\IP\Exception\InvalidIpAddressException
     * @throws \Darsyn\IP\Exception\WrongVersionException
     * @return \Darsyn\IP\IpInterface
     */
    abstract protected function createIpObject(string $ip);

    /** @inheritDoc */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getBinaryTypeDeclarationSQL(['length' => static::IP_LENGTH]);
    }

    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?IpInterface
    {
        if (\is_a($value, $this->getIpClass(), true)) {
            return $value;
        }

        // PostgreSQL will return the binary data as a resource instead of a
        // string (like MySQL).
        if (\is_resource($value) && \get_resource_type($value) === 'stream') {
            $value = \stream_get_contents($value);
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
     * @inheritDoc
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            /** @var \Darsyn\IP\IpInterface $ip */
            $ip = \is_a($value, $this->getIpClass(), true)
                ? $value
                : $this->createIpObject($value);
        } catch (InvalidIpAddressException $e) {
            throw ConversionException::conversionFailed($value, static::NAME);
        }
        return $ip->getBinary();
    }

    /** @inheritDoc */
    public function getName(): string
    {
        return self::NAME;
    }

    /** @inheritDoc */
    public function getBindingType(): int
    {
        return \PDO::PARAM_LOB;
    }

    /** @inheritDoc */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
