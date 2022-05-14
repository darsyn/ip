<?php

namespace Darsyn\IP\Version;

use Darsyn\IP\AbstractIP;
use Darsyn\IP\Exception;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Util\MbString;

/**
 * IPv4 Address
 *
 * IPv4 is an immutable value object for IP addresses, including some helper
 * functions for broadcast and network addresses, and whether its within the
 * range of another IP address according to a CIDR (subnet mask), etc.
 * This class deals solely with IPv4 addresses and will throw an
 * InvalidIpAddressException when IPv6 addresses are used.
 * Internally, the IP address is converted to a 4 byte binary sequence for easy
 * mathematical operations and consistency (for example, storing the IP address'
 * binary sequence in a fixed-length database column).
 *
 * @author    Zan Baldwin <hello@zanbaldwin.com>
 * @link      https://github.com/darsyn/ip
 * @copyright 2015 Zan Baldwin
 * @license   MIT/X11 <http://j.mp/mit-license>
 */
class IPv4 extends AbstractIP implements Version4Interface
{
    /**
     * {@inheritDoc}
     */
    public static function factory($ip)
    {
        try {
            // Convert from protocol notation to binary sequence.
            $binary = self::getProtocolFormatter()->pton($ip);
            // If the string was not 4 bytes long, then the IP supplied was
            // neither in protocol notation or binary sequence notation. Throw
            // an exception.
            if (MbString::getLength($binary) !== 4) {
                if (MbString::getLength($ip) !== 4) {
                    throw new Exception\WrongVersionException(4, 6, $ip);
                }
                $binary = $ip;
            }
        } catch(Exception\IpException $e) {
            throw new Exception\InvalidIpAddressException($ip, $e);
        }
        return new static($binary);
    }

    /**
     * {@inheritDoc}
     */
    public function getDotAddress()
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
        return 4;
    }

    /**
     * {@inheritDoc}
     */
    public function isLinkLocal()
    {
        return $this->inRange(new self(Binary::fromHex('a9fe0000')), 16);
    }

    /**
     * {@inheritDoc}
     */
    public function isLoopback()
    {
        return $this->inRange(new self(Binary::fromHex('7f000000')), 8);
    }

    /**
     * {@inheritDoc}
     */
    public function isMulticast()
    {
        return $this->inRange(new self(Binary::fromHex('e0000000')), 4);
    }

    /**
     * {@inheritDoc}
     */
    public function isPrivateUse()
    {
        return $this->inRange(new self(Binary::fromHex('0a000000')), 8)
            || $this->inRange(new self(Binary::fromHex('ac100000')), 12)
            || $this->inRange(new self(Binary::fromHex('c0a80000')), 16);
    }

    /**
     * {@inheritDoc}
     */
    public function isUnspecified()
    {
        return $this->getBinary() === "\0\0\0\0";
    }

    /**
     * {@inheritDoc}
     */
    public function isBenchmarking()
    {
        return $this->inRange(new self(Binary::fromHex('c6120000')), 15);
    }

    /**
     * {@inheritDoc}
     */
    public function isDocumentation()
    {
        return $this->inRange(new self(Binary::fromHex('c0000200')), 24)
            || $this->inRange(new self(Binary::fromHex('c6336400')), 24)
            || $this->inRange(new self(Binary::fromHex('cb007100')), 24);
    }

    /**
     * {@inheritDoc}
     */
    public function isPublicUse()
    {
        // Both 192.0.0.9 and 192.0.0.10 are globally routable, despite being in the future reserved block.
        if (in_array(Binary::toHex($this->getBinary()), ['c0000009', 'c000000a'], true)) {
            return true;
        }

        // The whole 0.0.0.0/8 block is not for public use.
        if ($this->inRange(new self(Binary::fromHex('00000000')), 8)) {
            return false;
        }

        // Addresses reserved for future protocols are not globally routable (different to reserved for future use).
        if ($this->inRange(new self(Binary::fromHex('c0000000')), 24)) {
            return false;
        }

        return !$this->isPrivateUse()
            && !$this->isLoopback()
            && !$this->isLinkLocal()
            && !$this->isBroadcast()
            && !$this->isShared()
            && !$this->isDocumentation()
            && !$this->isFutureReserved()
            && !$this->isBenchmarking();
    }

    /**
     * {@inheritDoc}
     */
    public function isBroadcast()
    {
        return $this->getBinary() === Binary::fromHex('ffffffff');
    }

    /**
     * {@inheritDoc}
     */
    public function isShared()
    {
        return $this->inRange(new self(Binary::fromHex('64400000')), 10);
    }

    /**
     * {@inheritDoc}
     */
    public function isFutureReserved()
    {
        return $this->getBinary() !== Binary::fromHex('ffffffff')
            && $this->inRange(new self(Binary::fromHex('f0000000')), 4);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return $this->getDotAddress();
    }
}
