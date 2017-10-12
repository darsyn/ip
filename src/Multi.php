<?php

namespace Darsyn\IP;

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
    private function getDefaultEmbeddingStrategy()
    {
        return self::$defaultEmbeddingStrategy ?: new MappedEmbeddingStrategy;
    }

    /**
     * Constructor
     *
     * @param string $ip
     * @param \Darsyn\IP\Strategy\EmbeddingStrategyInterface $strategy
     */
    public function __construct($ip, EmbeddingStrategyInterface $strategy = null)
    {
        $this->embeddingStrategy = $strategy ?: static::getDefaultEmbeddingStrategy();
        // If the IP address has been given in protocol notation, convert it to
        // a 4 byte binary sequence.
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ip = current(unpack('a4', inet_pton($ip)));
        }
        // If the IP address is a binary sequence of 4 bytes, then pack it into
        // a 16 byte IPv6 binary sequence according to the embedding strategy.
        if ($this->getBinaryLength($ip) === 4) {
            $ip = $this->embeddingStrategy->pack($ip);
        }
        parent::__construct($ip);
    }

    /**
     * {@inheritDoc}
     */
    public function getProtocolAppropriateAddress()
    {
        // If binary string contains an embedded IPv4 address, then extract it.
        $ip = $this->isEmbedded()
            ? $this->embeddingStrategy->extract($this->getBinary())
            : $this->getBinary();
        // Render the IP address in the correct notation according to its
        // protocol (based on how long the binary string is).
        return inet_ntop(pack('A' . $this->getBinaryLength($ip), $ip));
    }

    /**
     * @return string
     * @throws \Darsyn\IP\Exception\WrongVersionException
     */
    public function getSomethingAddress()
    {
        if ($this->isEmbedded()) {
            return inet_ntop(pack('A4', $this->embeddingStrategy->extract($this->getBinary())));
        }
        throw new Exception\WrongVersionException(4, 6, $this->getBinary());
    }

    /**
     * {@inheritDoc}
     */
    public function getVersion()
    {
        return $this->isEmbedded() ? 4 : 6;
    }

    /**
     * {@inheritDoc}
     */
    public function getNetworkIp($cidr)
    {
        if ($this->isCidrVersion4Appropriate($cidr) && $this->isEmbedded()) {
            return new static(
                (new IPv4($this->embeddingStrategy->extract($this->getBinary())))
                    ->getNetworkIp($cidr)
                    ->getBinary(),
                clone $this->embeddingStrategy
            );
        }
        return parent::getNetworkIp($cidr);
    }

    /**
     * {@inheritDoc}
     */
    public function getBroadcastIp($cidr)
    {
        if ($this->isCidrVersion4Appropriate($cidr) && $this->isEmbedded()) {
            return new static(
                (new IPv4($this->embeddingStrategy->extract($this->getBinary())))
                    ->getBroadcastIp($cidr)
                    ->getBinary(),
                clone $this->embeddingStrategy
            );
        }
        return parent::getBroadcastIp($cidr);
    }

    /**
     * Is IP Address In Range?
     *
     * Returns a boolean value depending on whether the IP address in question
     * is within the range of the target IP/CIDR combination.
     *
     * @param  \Darsyn\IP\IP $ip
     * @param  integer $cidr
     * @throws \Darsyn\IP\Exception\InvalidCidrException
     * @return bool
     */
    public function inRange(IpInterface $ip, $cidr)
    {
        // If both IP's (ours and theirs) are version 4 then attempt to compare
        // them as IPv4 ranges first (double checking that their IP address is
        // using the same embedding strategy as we are).
        // This purposefully will not work with comparing IPv4 addresses with
        // IPv4-embedded IPv6 addresses.
        if ($this->isVersion4() && $ip->isVersion4() && $this->embeddingStrategy->isEmbedded($ip->getBinary())) {
            $ours   = $this->embeddingStrategy->extract($this->getBinary());
            $theirs = $this->embeddingStrategy->extract($ip->getBinary());
            if ((new IPv4($ours))->inRange(new IPv4($theirs), $cidr)) {
                return true;
            }
        }
        // If they are not in range as IPv4 addresses, then just carry on and
        // compare them as normal IPv6 addresses.
        return parent::inRange($ip, $cidr);
    }

    /**
     * {@inheritDoc}
     */
    public function isEmbedded()
    {
        if (null === $this->embedded) {
            $this->embedded = $this->embeddingStrategy->isEmbedded($this->getBinary());
        }
        return $this->embedded;
    }

    /**
     * {@inheritDoc}
     */
    public function isLinkLocal()
    {
        return parent::isLinkLocal()
            || $this->isEmbedded()
            && (new IPv4($this->getBinary()))->isLinkLocal();
    }

    /**
     * {@inheritDoc}
     */
    public function isLoopback()
    {
        return parent::isLoopback()
            || (new IPv4($this->getBinary()))->isLoopback();
    }

    /**
     * {@inheritDoc}
     */
    public function isMulticast()
    {
        return parent::isMulticast()
            || $this->isEmbedded()
            && (new IPv4($this->getBinary()))->isMulticast();
    }

    /**
     * {@inheritDoc}
     */
    public function isPrivateUse()
    {
        return parent::isPrivateUse()
            || $this->isEmbedded()
            && (new IPv4($this->getBinary()))->isPrivateUse();
    }

    /**
     * {@inheritDoc}
     */
    public function isUnspecified()
    {
        return parent::isUnspecified()
            || $this->isEmbedded()
            && (new IPv4($this->getBinary()))->isUnspecified();
    }

    /**
     * Is the CIDR provided appropriate for use with IPv4 addresses?
     *
     * @param int $cidr
     * @return bool
     */
    private function isCidrVersion4Appropriate($cidr)
    {
        return is_int($cidr) && $cidr <= 32;
    }
}
