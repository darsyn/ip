<?php

namespace Darsyn\IP\Version;

use Darsyn\IP\Exception;
use Darsyn\IP\IpInterface;
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
 * fixed-length database column). in the same column in a database).
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

    /**
     * {@inheritDoc}
     */
    public static function setDefaultEmbeddingStrategy(EmbeddingStrategyInterface $strategy)
    {
        self::$defaultEmbeddingStrategy = $strategy;
    }

    /**
     * Get the default embedding strategy set. Default to the IPv4-mapped IPv6
     * embedding strategy if the user has not set one globally.
     *
     * @return \Darsyn\IP\Strategy\EmbeddingStrategyInterface
     */
    private static function getDefaultEmbeddingStrategy()
    {
        return self::$defaultEmbeddingStrategy ?: new MappedEmbeddingStrategy;
    }

    /**
     * {@inheritDoc}
     * @param \Darsyn\IP\Strategy\EmbeddingStrategyInterface $strategy
     */
    public static function factory($ip, EmbeddingStrategyInterface $strategy = null)
    {
        // We need a strategy to pack version 4 addresses.
        $strategy = $strategy ?: self::getDefaultEmbeddingStrategy();

        try {
            // Convert from protocol notation to binary sequence.
            $binary = self::getProtocolFormatter()->pton($ip);

            // If the IP address is a binary sequence of 4 bytes, then pack it into
            // a 16 byte IPv6 binary sequence according to the embedding strategy.
            if (MbString::getLength($binary) === 4) {
                $binary = $strategy->pack($binary);
            }
        } catch (Exception\IpException $e) {
            throw new Exception\InvalidIpAddressException($ip, $e);
        }
        return new static($binary, $strategy);
    }

    /**
     * {@inheritDoc}
     * @param \Darsyn\IP\Strategy\EmbeddingStrategyInterface|null $strategy
     */
    protected function __construct($ip, EmbeddingStrategyInterface $strategy = null)
    {
        // Fallback to default in case this instance was created from static in
        // the abstract IP class.
        $this->embeddingStrategy = $strategy ?: self::getDefaultEmbeddingStrategy();
        parent::__construct($ip);
    }

    /** {@inheritDoc} */
    public function getProtocolAppropriateAddress()
    {
        // If binary string contains an embedded IPv4 address, then extract it.
        $ip = $this->isEmbedded()
            ? $this->getShortBinary()
            : $this->getBinary();
        // Render the IP address in the correct notation according to its
        // protocol (based on how long the binary string is).
        return self::getProtocolFormatter()->ntop($ip);
    }

    /**
     * @throws \Darsyn\IP\Exception\WrongVersionException
     * @throws \Darsyn\IP\Exception\IpException
     * @return string
     */
    public function getDotAddress()
    {
        if ($this->isEmbedded()) {
            try {
                return self::getProtocolFormatter()->ntop($this->getShortBinary());
            } catch (Exception\Formatter\FormatException $e) {
                throw new Exception\IpException('An unknown error occured internally.', 0, $e);
            }
        }
        throw new Exception\WrongVersionException(4, 6, (string) $this);
    }

    /** {@inheritDoc} */
    public function getVersion()
    {
        return $this->isEmbedded() ? 4 : 6;
    }

    /** {@inheritDoc} */
    public function getNetworkIp($cidr)
    {
        try {
            if ($this->isVersion4WithAppropriateCidr($cidr)) {
                $v4 = (new IPv4($this->getShortBinary()))->getNetworkIp($cidr)->getBinary();
                return new static(
                    $this->embeddingStrategy->pack($v4),
                    clone $this->embeddingStrategy
                );
            }
        } catch (Exception\IpException $e) {
        }
        return new static(parent::getNetworkIp($cidr)->getBinary(), clone $this->embeddingStrategy);
    }

    /** {@inheritDoc} */
    public function getBroadcastIp($cidr)
    {
        try {
            if ($this->isVersion4WithAppropriateCidr($cidr)) {
                $v4 = (new IPv4($this->getShortBinary()))->getBroadcastIp($cidr)->getBinary();
                return new static(
                    $this->embeddingStrategy->pack($v4),
                    clone $this->embeddingStrategy
                );
            }
        } catch (Exception\IpException $e) {
        }
        return new static(parent::getBroadcastIp($cidr)->getBinary(), clone $this->embeddingStrategy);
    }

    /** {@inheritDoc} */
    public function inRange(IpInterface $ip, $cidr)
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

    /** {@inheritDoc} */
    public function getCommonCidr(IpInterface $ip)
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

    /** {@inheritDoc} */
    public function isEmbedded()
    {
        if (null === $this->embedded) {
            $this->embedded = $this->embeddingStrategy->isEmbedded($this->getBinary());
        }
        return $this->embedded;
    }

    /** {@inheritDoc} */
    public function isLinkLocal()
    {
        return $this->isEmbedded()
            ? (new IPv4($this->getShortBinary()))->isLinkLocal()
            : parent::isLinkLocal();
    }

    /** {@inheritDoc} */
    public function isLoopback()
    {
        return $this->isEmbedded()
            ? (new IPv4($this->getShortBinary()))->isLoopback()
            : parent::isLoopback();
    }

    /** * {@inheritDoc} */
    public function isMulticast()
    {
        return $this->isEmbedded()
            ? (new IPv4($this->getShortBinary()))->isMulticast()
            : parent::isMulticast();
    }

    /** {@inheritDoc} */
    public function isPrivateUse()
    {
        return $this->isEmbedded()
            ? (new IPv4($this->getShortBinary()))->isPrivateUse()
            : parent::isPrivateUse();
    }

    /** {@inheritDoc} */
    public function isUnspecified()
    {
        return $this->isEmbedded()
            ? (new IPv4($this->getShortBinary()))->isUnspecified()
            : parent::isUnspecified();
    }

    /** {@inheritDoc} */
    public function isBenchmarking()
    {
        return $this->isEmbedded()
            ? (new IPv4($this->getShortBinary()))->isBenchmarking()
            : parent::isBenchmarking();
    }

    /** {@inheritDoc} */
    public function isDocumentation()
    {
        return $this->isEmbedded()
            ? (new IPv4($this->getShortBinary()))->isDocumentation()
            : parent::isDocumentation();
    }

    /** {@inheritDoc} */
    public function isPublicUse()
    {
        return $this->isEmbedded()
            ? (new IPv4($this->getShortBinary()))->isPublicUse()
            : parent::isPublicUse();
    }

    /**
     * @inheritDoc
     */
    public function isUniqueLocal()
    {
        if ($this->isEmbedded()) {
            throw new Exception\WrongVersionException(6, 4, (string) $this);
        }
        return parent::isUniqueLocal();
    }

    /**
     * @inheritDoc
     */
    public function isUnicast()
    {
        if ($this->isEmbedded()) {
            throw new Exception\WrongVersionException(6, 4, (string) $this);
        }
        return parent::isUnicast();
    }

    /**
     * @inheritDoc
     */
    public function isUnicastGlobal()
    {
        if ($this->isEmbedded()) {
            throw new Exception\WrongVersionException(6, 4, (string) $this);
        }
        return parent::isUnicastGlobal();
    }

    /**
     * {@inheritDoc}
     */
    public function isBroadcast()
    {
        if ($this->isEmbedded()) {
            return (new IPv4($this->getShortBinary()))->isBroadcast();
        }
        throw new Exception\WrongVersionException(4, 6, (string) $this);
    }

    /**
     * {@inheritDoc}
     */
    public function isShared()
    {
        if ($this->isEmbedded()) {
            return (new IPv4($this->getShortBinary()))->isShared();
        }
        throw new Exception\WrongVersionException(4, 6, (string) $this);
    }

    /**
     * {@inheritDoc}
     */
    public function isFutureReserved()
    {
        if ($this->isEmbedded()) {
            return (new IPv4($this->getShortBinary()))->isFutureReserved();
        }
        throw new Exception\WrongVersionException(4, 6, (string) $this);
    }

    /**
     * @throws \Darsyn\IP\Exception\Strategy\ExtractionException
     * @return string
     */
    private function getShortBinary()
    {
        return $this->embeddingStrategy->extract($this->getBinary());
    }

    /**
     * Can the supplied CIDR and current version be considered as an IPv4 operation?
     *
     * @param int $cidr
     * @return bool
     */
    private function isVersion4WithAppropriateCidr($cidr)
    {
        return \is_int($cidr) && $cidr <= 32 && $this->isVersion4();
    }

    /**
     * Can the supplied and current IP be considered as an IPv4 operation?
     *
     * @param \Darsyn\IP\IpInterface $ip
     * @return bool
     */
    private function isVersion4CompatibleWithCurrentStrategy(IpInterface $ip)
    {
        return $this->isVersion4() && $ip->isVersion4() && $this->embeddingStrategy->isEmbedded($ip->getBinary());
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return $this->getProtocolAppropriateAddress();
    }
}
