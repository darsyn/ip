<?php

namespace Darsyn\IP\Version;

use Darsyn\IP\AbstractIP;
use Darsyn\IP\Exception;
use Darsyn\IP\Strategy\EmbeddingStrategyInterface;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Util\MbString;

/**
 * IPv6 Address
 *
 * IPv6 is an immutable value object for IP addresses, including some helper
 * functions for broadcast and network addresses, and whether its within the
 * range of another IP address according to a CIDR (subnet mask), etc.
 * This class deals solely with IPv6 addresses and will throw an
 * InvalidIpAddressException when IPv4 addresses are used.
 * Internally, the IP address is converted to a 16 byte binary sequence for easy
 * mathematical operations and consistency (for example, storing the IP address'
 * binary sequence in a fixed-length database column).
 *
 * @author    Zan Baldwin <hello@zanbaldwin.com>
 * @link      https://github.com/darsyn/ip
 * @copyright 2015 Zan Baldwin
 * @license   MIT/X11 <http://j.mp/mit-license>
 */
class IPv6 extends AbstractIP implements Version6Interface
{

    /**
     * {@inheritDoc}
     */
    public static function factory($ip)
    {
        try {
            // Convert from protocol notation to binary sequence.
            $binary = self::getProtocolFormatter()->pton($ip);
            // If the string was not 4 bytes long, then the IP supplied was neither
            // in protocol notation or binary sequence notation. Throw an exception.
            if (MbString::getLength($binary) !== 16) {
                throw new Exception\WrongVersionException(6, 4, $ip);
            }
        } catch (Exception\IpException $e) {
            throw new Exception\InvalidIpAddressException($ip, $e);
        }
        return new static($binary);
    }

    /**
     * @param string $ip
     * @param \Darsyn\IP\Strategy\EmbeddingStrategyInterface|null $strategy
     * @throws \Darsyn\IP\Exception\InvalidIpAddressException
     * @throws \Darsyn\IP\Exception\WrongVersionException
     * @return static
     */
    public static function fromEmbedded($ip, EmbeddingStrategyInterface $strategy = null)
    {
        return new static(Multi::factory($ip, $strategy)->getBinary());
    }

    /**
     * {@inheritDoc}
     */
    public function getExpandedAddress()
    {
        // Convert the 16-byte binary sequence into a hexadecimal-string
        // representation, insert a colon between every block of 4 characters,
        // and return the resulting IP address in full IPv6 protocol notation.
        $expanded = \preg_replace('/([a-fA-F0-9]{4})/', '$1:', Binary::toHex($this->getBinary()));
        return MbString::subString(\is_string($expanded) ? $expanded : '', 0, -1);
    }

    /**
     * {@inheritDoc}
     */
    public function getCompactedAddress()
    {
        try {
            return self::getProtocolFormatter()->ntop($this->getBinary());
        } catch (Exception\Formatter\FormatException $e) {
            throw new Exception\IpException('An unknown error occured internally.', 0, $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getVersion()
    {
        return 6;
    }

    /**
     * {@inheritDoc}
     */
    public function isLinkLocal()
    {
        return $this->inRange(new self(Binary::fromHex('fe800000000000000000000000000000')), 10);
    }

    /**
     * {@inheritDoc}
     */
    public function isLoopback()
    {
        return $this->inRange(new self(Binary::fromHex('00000000000000000000000000000001')), 128);
    }

    /**
     * {@inheritDoc}
     */
    public function isMulticast()
    {
        return $this->inRange(new self(Binary::fromHex('ff000000000000000000000000000000')), 8);
    }

    /**
     * {@inheritDoc}
     */
    public function getMulticastScope()
    {
        if (!$this->isMulticast()) {
            return null;
        }
        $firstSegment = MbString::subString($this->getBinary(), 0, 2);
        return (int) hexdec(Binary::toHex($firstSegment & Binary::fromHex('000f')));
    }

    /**
     * {@inheritDoc}
     */
    public function isPrivateUse()
    {
        return $this->inRange(new self(Binary::fromHex('fd000000000000000000000000000000')), 8);
    }

    /**
     * {@inheritDoc}
     */
    public function isUnspecified()
    {
        return $this->getBinary() === "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
    }

    /**
     * @inheritDoc
     */
    public function isBenchmarking()
    {
        return $this->inRange(new self(Binary::fromHex('20010002000000000000000000000000')), 48);
    }

    /**
     * @inheritDoc
     */
    public function isDocumentation()
    {
        return $this->inRange(new self(Binary::fromHex('20010db8000000000000000000000000')), 32);
    }

    /**
     * @inheritDoc
     */
    public function isPublicUse()
    {
        return $this->getMulticastScope() === self::MULTICAST_GLOBAL || $this->isUnicastGlobal();
    }

    /**
     * @inheritDoc
     */
    public function isUniqueLocal()
    {
        return $this->inRange(new self(Binary::fromHex('fc000000000000000000000000000000')), 7);
    }

    /**
     * @inheritDoc
     */
    public function isUnicast()
    {
        return !$this->isMulticast();
    }

    /**
     * @inheritDoc
     */
    public function isUnicastGlobal()
    {
        return $this->isUnicast()
            && !$this->isLoopback()
            && !$this->isLinkLocal()
            && !$this->isUniqueLocal()
            && !$this->isUnspecified()
            && !$this->isDocumentation();
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return $this->getCompactedAddress();
    }
}
