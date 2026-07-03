<?php

declare(strict_types=1);

namespace Darsyn\IP\Version;

use Darsyn\IP\Exception;
use Darsyn\IP\IpInterface;
use Darsyn\IP\Strategy\CanonicalEmbeddingInterface;
use Darsyn\IP\Strategy\EmbeddingStrategyInterface;
use Darsyn\IP\Strategy\Mapped as MappedEmbeddingStrategy;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Util\MbString;

/**
 * Multi-version IP Address
 *
 * IP is an immutable value object that provides several notations of the same
 * IP value, including some helper functions for broadcast and network
 * addresses, and whether its within the range of another IP address according
 * to a CIDR (subnet mask), etc.
 * Although it deals with both IPv4 and IPv6 notations, it makes no distinction
 * between the two protocol formats as it converts both of them to a 16-byte
 * binary sequence for easy mathematical operations and consistency (for
 * example, storing both IPv4 and IPv6 addresses' binary sequences in a
 * fixed-length database column).
 *
 * @author    Zan Baldwin <hello@zanbaldwin.com>
 * @link      https://github.com/darsyn/ip
 * @copyright 2015 Zan Baldwin
 * @license   MIT/X11 <http://j.mp/mit-license>
 */
class Multi extends IPv6 implements MultiVersionInterface
{
    /** @var \Darsyn\IP\Strategy\EmbeddingStrategyInterface|null $defaultEmbeddingStrategy */
    private static $defaultEmbeddingStrategy;

    /** @var \Darsyn\IP\Strategy\EmbeddingStrategyInterface $embeddingStrategy */
    private $embeddingStrategy;

    /** @var bool $embedded */
    private $embedded;

    public static function setDefaultEmbeddingStrategy(EmbeddingStrategyInterface $strategy): void
    {
        self::$defaultEmbeddingStrategy = $strategy;
    }

    /**
     * Get the default embedding strategy set. Default to the IPv4-mapped IPv6
     * embedding strategy if the user has not set one globally.
     */
    private static function getDefaultEmbeddingStrategy(): EmbeddingStrategyInterface
    {
        return self::$defaultEmbeddingStrategy ?: new MappedEmbeddingStrategy();
    }

    /** Graceful degradation for a user-defined embedding strategy that doesn't implement the CanonicalEmbeddingInterface. */
    private static function packIntoCanonical(EmbeddingStrategyInterface $strategy, string $binary): string
    {
        if ($strategy instanceof CanonicalEmbeddingInterface) {
            return $strategy->packIntoCanonical($binary);
        }
        /** @phpstan-ignore method.deprecated */
        return $strategy->pack($binary);
    }

    /** Graceful degradation for a user-defined embedding strategy that doesn't implement the CanonicalEmbeddingInterface. */
    private static function packIntoNonCanonical(EmbeddingStrategyInterface $strategy, string $ipv6, string $ipv4): string
    {
        if ($strategy instanceof CanonicalEmbeddingInterface) {
            return $strategy->packIntoNonCanonical($ipv6, $ipv4);
        }
        /** @phpstan-ignore method.deprecated */
        return $strategy->pack($ipv4);
    }

    /** @deprecated Use fromProtocol() or fromBinary() instead. */
    public static function factory(string $ip, ?EmbeddingStrategyInterface $strategy = null): self
    {
        // We need a strategy to pack version 4 addresses.
        $strategy = $strategy ?: self::getDefaultEmbeddingStrategy();

        try {
            // Convert from protocol notation to binary sequence.
            $binary = self::getProtocolFormatter()->pton($ip);

            // If the IP address is a binary sequence of 4 bytes, then pack it into
            // a 16 byte IPv6 binary sequence according to the embedding strategy.
            if (4 === MbString::getLength($binary)) {
                $binary = self::packIntoCanonical($strategy, $binary);
            }
        } catch (Exception\IpException $e) {
            throw new Exception\InvalidIpAddressException($ip, $e);
        }
        return new static($binary, $strategy);
    }

    public static function fromProtocol(string $ip, ?EmbeddingStrategyInterface $strategy = null)
    {
        $strategy = $strategy ?: self::getDefaultEmbeddingStrategy();
        try {
            $binary = self::getProtocolFormatter()->pton($ip);
        } catch (Exception\IpException $e) {
            throw new Exception\InvalidIpAddressException($ip, $e);
        }
        // (see rant in IPv4::fromProtocol).
        if ($binary === $ip) {
            throw new Exception\InvalidIpAddressException($ip);
        }
        $length = MbString::getLength($binary);
        if (4 === $length) {
            $binary = self::packIntoCanonical($strategy, $binary);
        } elseif (16 !== $length) {
            throw new Exception\InvalidBinaryException($binary);
        }
        return new static($binary, $strategy);
    }

    public static function tryFromProtocol(string $ip, ?EmbeddingStrategyInterface $strategy = null)
    {
        try {
            return static::fromProtocol($ip, $strategy);
        } catch (Exception\InvalidIpAddressException $e) {
            return null;
        }
    }

    public static function fromBinary(string $binary, ?EmbeddingStrategyInterface $strategy = null)
    {
        $strategy = $strategy ?: self::getDefaultEmbeddingStrategy();
        $length = MbString::getLength($binary);
        if (4 === $length) {
            $binary = self::packIntoCanonical($strategy, $binary);
        } elseif (16 !== $length) {
            throw new Exception\InvalidBinaryException($binary);
        }
        return new static($binary, $strategy);
    }

    public static function tryFromBinary(string $binary, ?EmbeddingStrategyInterface $strategy = null)
    {
        try {
            return static::fromBinary($binary, $strategy);
        } catch (Exception\InvalidIpAddressException $e) {
            return null;
        }
    }

    public static function fromHex(string $hex, ?EmbeddingStrategyInterface $strategy = null)
    {
        try {
            $binary = Binary::fromHex($hex);
        } catch (\InvalidArgumentException $e) {
            throw new Exception\InvalidIpAddressException($hex, $e);
        }
        return static::fromBinary($binary, $strategy);
    }

    public static function tryFromHex(string $hex, ?EmbeddingStrategyInterface $strategy = null)
    {
        try {
            return static::fromHex($hex, $strategy);
        } catch (Exception\InvalidIpAddressException $e) {
            return null;
        }
    }

    public static function fromInteger(int $integer, ?EmbeddingStrategyInterface $strategy = null)
    {
        // Reuse IPv4's range validation; the resulting 4-byte sequence is
        // packed into 16 bytes by fromBinary() via the embedding strategy.
        return static::fromBinary(IPv4::fromInteger($integer)->getBinary(), $strategy);
    }

    public static function tryFromInteger(int $integer, ?EmbeddingStrategyInterface $strategy = null)
    {
        try {
            return static::fromInteger($integer, $strategy);
        } catch (Exception\InvalidIpAddressException $e) {
            return null;
        }
    }

    public static function fromIntegerString(string $integer, ?EmbeddingStrategyInterface $strategy = null)
    {
        try {
            $binary = Binary::fromDecimalString($integer, 16);
        } catch (\InvalidArgumentException|Exception\OverflowException $e) {
            throw new Exception\InvalidIpAddressException($integer, $e);
        }
        return static::fromBinary($binary, $strategy);
    }

    public static function tryFromIntegerString(string $integer, ?EmbeddingStrategyInterface $strategy = null)
    {
        try {
            return static::fromIntegerString($integer, $strategy);
        } catch (Exception\InvalidIpAddressException $e) {
            return null;
        }
    }

    public static function isValid(string $ip, ?EmbeddingStrategyInterface $strategy = null): bool
    {
        return null !== static::tryFromProtocol($ip, $strategy);
    }

    protected function __construct(string $ip, ?EmbeddingStrategyInterface $strategy = null)
    {
        // Fallback to default in case this instance was created from static in
        // the abstract IP class.
        $this->embeddingStrategy = $strategy ?: self::getDefaultEmbeddingStrategy();
        parent::__construct($ip);
    }

    public function getProtocolAppropriateAddress(/* ?ProtocolFormatterInterface $formatter = null */): string
    {
        // If binary string contains an embedded IPv4 address, then extract it.
        $ip = $this->isEmbedded()
            ? $this->getShortBinary()
            : $this->getBinary();
        // Render the IP address in the correct notation according to its
        // protocol (based on how long the binary string is).
        return self::resolveProtocolFormatter(\func_get_args())->ntop($ip);
    }

    /**
     * @throws \Darsyn\IP\Exception\WrongVersionException
     * @throws \Darsyn\IP\Exception\IpException
     */
    public function getDotAddress(/* ?ProtocolFormatterInterface $formatter = null */): string
    {
        // Resolve the per-call formatter argument before the version check so a
        // deprecated (non-formatter) argument is flagged regardless of embedded state.
        $formatter = self::resolveProtocolFormatter(\func_get_args());
        if ($this->isEmbedded()) {
            try {
                return $formatter->ntop($this->getShortBinary());
            } catch (Exception\Formatter\FormatException $e) {
                throw new Exception\IpException('An unknown error occurred internally.', 0, $e);
            }
        }
        throw new Exception\WrongVersionException(4, 6, (string) $this);
    }

    /** @throws \Darsyn\IP\Exception\WrongVersionException */
    public function toInteger(): int
    {
        if ($this->isEmbedded()) {
            return (new IPv4($this->getShortBinary()))->toInteger();
        }
        throw new Exception\WrongVersionException(4, 6, (string) $this);
    }

    public function getOctets(): array
    {
        return $this->isEmbedded()
            ? (new IPv4($this->getShortBinary()))->getOctets()
            : parent::getOctets();
    }

    public function getSegments(): array
    {
        if ($this->isEmbedded()) {
            throw new Exception\WrongVersionException(6, 4, (string) $this);
        }
        return parent::getSegments();
    }

    public function getVersion(): int
    {
        return $this->isEmbedded() ? 4 : 6;
    }

    public function getNetworkIp(int $cidr): self
    {
        try {
            if ($this->isVersion4WithAppropriateCidr($cidr)) {
                $v4 = (new IPv4($this->getShortBinary()))->getNetworkIp($cidr)->getBinary();
                return new static(
                    self::packIntoCanonical($this->embeddingStrategy, $v4),
                    clone $this->embeddingStrategy
                );
            }
        } catch (Exception\IpException $e) {
        }
        return new static(parent::getNetworkIp($cidr)->getBinary(), clone $this->embeddingStrategy);
    }

    public function getBroadcastIp(int $cidr): self
    {
        try {
            if ($this->isVersion4WithAppropriateCidr($cidr)) {
                $v4 = (new IPv4($this->getShortBinary()))->getBroadcastIp($cidr)->getBinary();
                return new static(
                    self::packIntoCanonical($this->embeddingStrategy, $v4),
                    clone $this->embeddingStrategy
                );
            }
        } catch (Exception\IpException $e) {
        }
        return new static(parent::getBroadcastIp($cidr)->getBinary(), clone $this->embeddingStrategy);
    }

    public function offset(int $offset): self
    {
        if ($this->isEmbedded()) {
            $v4 = (new IPv4($this->getShortBinary()))->offset($offset)->getBinary();
            return new static(
                self::packIntoNonCanonical($this->embeddingStrategy, $this->getBinary(), $v4),
                clone $this->embeddingStrategy
            );
        }
        return new static(parent::offset($offset)->getBinary(), clone $this->embeddingStrategy);
    }

    public function inRange(IpInterface $ip, int $cidr): bool
    {
        try {
            if ($this->isVersion4WithAppropriateCidr($cidr) && $this->isVersion4CompatibleWithCurrentStrategy($ip)) {
                $ours = $this->getShortBinary();
                $theirs = $this->embeddingStrategy->extract($ip->getBinary());
                return (new IPv4($ours))->inRange(new IPv4($theirs), $cidr);
            }
        } catch (Exception\IpException $e) {
            // If an exception was thrown, the two IP addresses were incompatible
            // and should not have been checked as IPv4 addresses, fallback to
            // performing the operation as IPv6 addresses.
        }
        return parent::inRange($ip, $cidr);
    }

    public function getCommonCidr(IpInterface $ip): int
    {
        try {
            if ($this->isVersion4CompatibleWithCurrentStrategy($ip)) {
                $ours = $this->getShortBinary();
                $theirs = $this->embeddingStrategy->extract($ip->getBinary());
                return (new IPv4($ours))->getCommonCidr(new IPv4($theirs));
            }
        } catch (Exception\IpException $e) {
            // If an exception was thrown, the two IP addresses were incompatible
            // and should not have been checked as IPv4 addresses, fallback to
            // performing the operation as IPv6 addresses.
        }
        return parent::getCommonCidr($ip);
    }

    /** @not-deprecated IpInterface deprecated in favour of Contracts\StrategyDetectionInterface. */
    public function isEmbedded(): bool
    {
        if (null === $this->embedded) {
            $this->embedded = $this->embeddingStrategy->isEmbedded($this->getBinary());
        }
        return $this->embedded;
    }

    public function getEmbeddedIp(?EmbeddingStrategyInterface $strategy = null): IPv4
    {
        // An explicit strategy overrides the one attached to this instance.
        $strategy = $strategy ?: $this->embeddingStrategy;
        if (!$strategy->isEmbedded($this->getBinary())) {
            throw new Exception\WrongVersionException(4, 6, (string) $this);
        }
        return IPv4::fromBinary($strategy->extract($this->getBinary()));
    }

    public function isLinkLocal(): bool
    {
        return $this->isEmbedded()
            ? (new IPv4($this->getShortBinary()))->isLinkLocal()
            : parent::isLinkLocal();
    }

    public function isLoopback(): bool
    {
        return $this->isEmbedded()
            ? (new IPv4($this->getShortBinary()))->isLoopback()
            : parent::isLoopback();
    }

    public function isMulticast(): bool
    {
        return $this->isEmbedded()
            ? (new IPv4($this->getShortBinary()))->isMulticast()
            : parent::isMulticast();
    }

    public function isPrivateUse(): bool
    {
        return $this->isEmbedded()
            ? (new IPv4($this->getShortBinary()))->isPrivateUse()
            : parent::isPrivateUse();
    }

    public function isUnspecified(): bool
    {
        return $this->isEmbedded()
            ? (new IPv4($this->getShortBinary()))->isUnspecified()
            : parent::isUnspecified();
    }

    public function isBenchmarking(): bool
    {
        return $this->isEmbedded()
            ? (new IPv4($this->getShortBinary()))->isBenchmarking()
            : parent::isBenchmarking();
    }

    public function isDocumentation(): bool
    {
        return $this->isEmbedded()
            ? (new IPv4($this->getShortBinary()))->isDocumentation()
            : parent::isDocumentation();
    }

    /** @deprecated Use isGloballyReachable() instead. */
    public function isPublicUse(): bool
    {
        return $this->isGloballyReachable();
    }

    public function isGloballyReachable(): bool
    {
        return $this->isEmbedded()
            ? (new IPv4($this->getShortBinary()))->isGloballyReachable()
            : parent::isGloballyReachable();
    }

    public function isUniqueLocal(): bool
    {
        if ($this->isEmbedded()) {
            throw new Exception\WrongVersionException(6, 4, (string) $this);
        }
        return parent::isUniqueLocal();
    }

    public function isUnicast(): bool
    {
        if ($this->isEmbedded()) {
            throw new Exception\WrongVersionException(6, 4, (string) $this);
        }
        return parent::isUnicast();
    }

    public function isUnicastGlobal(): bool
    {
        if ($this->isEmbedded()) {
            throw new Exception\WrongVersionException(6, 4, (string) $this);
        }
        return parent::isUnicastGlobal();
    }

    public function isBroadcast(): bool
    {
        if ($this->isEmbedded()) {
            return (new IPv4($this->getShortBinary()))->isBroadcast();
        }
        throw new Exception\WrongVersionException(4, 6, (string) $this);
    }

    public function isShared(): bool
    {
        if ($this->isEmbedded()) {
            return (new IPv4($this->getShortBinary()))->isShared();
        }
        throw new Exception\WrongVersionException(4, 6, (string) $this);
    }

    public function isFutureReserved(): bool
    {
        if ($this->isEmbedded()) {
            return (new IPv4($this->getShortBinary()))->isFutureReserved();
        }
        throw new Exception\WrongVersionException(4, 6, (string) $this);
    }

    /** @throws \Darsyn\IP\Exception\Strategy\ExtractionException */
    private function getShortBinary(): string
    {
        return $this->embeddingStrategy->extract($this->getBinary());
    }

    /** Can the supplied CIDR and current version be considered as an IPv4 operation? */
    private function isVersion4WithAppropriateCidr(int $cidr): bool
    {
        return $cidr <= 32 && $this->isVersion4();
    }

    /** Can the supplied and current IP be considered as an IPv4 operation? */
    private function isVersion4CompatibleWithCurrentStrategy(IpInterface $ip): bool
    {
        return $this->isVersion4() && $ip->isVersion4() && $this->embeddingStrategy->isEmbedded($ip->getBinary());
    }

    public function toString(): string
    {
        return $this->getProtocolAppropriateAddress();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
