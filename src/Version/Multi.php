<?php

namespace Darsyn\IP\Version;

use Darsyn\IP\Binary;
use Darsyn\IP\Exception;
use Darsyn\IP\IpInterface;
use Darsyn\IP\Strategy\EmbeddingStrategyInterface;
use Darsyn\IP\Strategy\Mapped as MappedEmbeddingStrategy;

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
    /** @var \Darsyn\IP\Strategy\EmbeddingStrategyInterface $defaultEmbeddingStrategy */
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
        $strategy = $strategy ?: static::getDefaultEmbeddingStrategy();

        try {
            // Convert from protocol notation to binary sequence.
            $binary = self::getProtocolFormatter()->pton($ip);

            // If the IP address is a binary sequence of 4 bytes, then pack it into
            // a 16 byte IPv6 binary sequence according to the embedding strategy.
            if (Binary::getLength($binary) === 4) {
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
                throw new Exception\IpException('An unknown error occured internally.', null, $e);
            }
        }
        throw new Exception\WrongVersionException(4, 6, $this->getBinary());
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
            if ($this->isCidrVersion4Appropriate($cidr) && $this->isEmbedded()) {
                $v4 = (new IPv4($this->getShortBinary()))->getNetworkIp($cidr)->getBinary();
                return new static(
                    $this->embeddingStrategy->pack($v4),
                    clone $this->embeddingStrategy
                );
            }
        } catch (Exception\IpException $e) {
        }
        return parent::getNetworkIp($cidr);
    }

    /** {@inheritDoc} */
    public function getBroadcastIp($cidr)
    {
        try {
            if ($this->isCidrVersion4Appropriate($cidr) && $this->isEmbedded()) {
                $v4 = (new IPv4($this->getShortBinary()))->getBroadcastIp($cidr)->getBinary();
                return new static(
                    $this->embeddingStrategy->pack($v4),
                    clone $this->embeddingStrategy
                );
            }
        } catch (Exception\IpException $e) {
        }
        return parent::getBroadcastIp($cidr);
    }

    /** {@inheritDoc} */
    public function inRange(IpInterface $ip, $cidr)
    {
        // If both IP's (ours and theirs) are version 4 according to OUR
        // embedding strategy then attempt to compare them as IPv4 ranges first.
        // This purposefully will not work with comparing IPv4 addresses with
        // IPv4-embedded IPv6 addresses.
        try {
            if ($this->isVersion4() && $ip->isVersion4() && $this->embeddingStrategy->isEmbedded($ip->getBinary())) {
                $ours = $this->getShortBinary();
                $theirs = $this->embeddingStrategy->extract($ip->getBinary());
                if ((new IPv4($ours))->inRange(new IPv4($theirs), $cidr)) {
                    return true;
                }
            }
        } catch (Exception\Strategy\ExtractionException $e) {
        } catch (Exception\InvalidIpAddressException $e) {
        }
        // If they are not in range as IPv4 addresses, then just carry on and
        // compare them as normal IPv6 addresses.
        return parent::inRange($ip, $cidr);
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
        return parent::isLinkLocal()
            || $this->isEmbedded()
            && (new IPv4($this->getShortBinary()))->isLinkLocal();
    }

    /** {@inheritDoc} */
    public function isLoopback()
    {
        return parent::isLoopback()
            || $this->isEmbedded()
            && (new IPv4($this->getShortBinary()))->isLoopback();
    }

    /** * {@inheritDoc} */
    public function isMulticast()
    {
        return parent::isMulticast()
            || $this->isEmbedded()
            && (new IPv4($this->getShortBinary()))->isMulticast();
    }

    /** {@inheritDoc} */
    public function isPrivateUse()
    {
        return parent::isPrivateUse()
            || $this->isEmbedded()
            && (new IPv4($this->getShortBinary()))->isPrivateUse();
    }

    /** {@inheritDoc} */
    public function isUnspecified()
    {
        return parent::isUnspecified()
            || $this->isEmbedded()
            && (new IPv4($this->getShortBinary()))->isUnspecified();
    }

    private function getShortBinary()
    {
        return $this->embeddingStrategy->extract($this->getBinary());
    }

    /**
     * Is the CIDR provided appropriate for use with IPv4 addresses?
     *
     * @param int $cidr
     * @return bool
     */
    private function isCidrVersion4Appropriate($cidr)
    {
        return \is_int($cidr) && $cidr <= 32;
    }
}
