<?php

namespace Darsyn\IP\Version;

use Darsyn\IP\AbstractIP;
use Darsyn\IP\Exception;

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
    public function __construct($ip)
    {
        // If the IP address has been given in protocol notation, convert it to
        // a 4 byte binary sequence.
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ip = current(unpack('a4', inet_pton($ip)));
        }
        // If the string was not 4 bytes long, then the IP supplied was neither
        // in protocol notation or binary sequence notation. Throw an exception.
        if (!is_string($ip) || $this->getBinaryLength($ip) !== 4) {
            throw new Exception\WrongVersionException(4, null, $ip);
        }
        parent::__construct($ip);
    }

    /**
     * {@inheritDoc}
     */
    public function getDotAddress()
    {
        return inet_ntop(pack('A4', $this->getBinary()));
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
        return $this->inRange(new static('169.254.0.0'), 16);
    }

    /**
     * {@inheritDoc}
     */
    public function isLoopback()
    {
        return $this->inRange(new static('127.0.0.0'), 8);
    }

    /**
     * {@inheritDoc}
     */
    public function isMulticast()
    {
        return $this->inRange(new static('224.0.0.0'), 4);
    }

    /**
     * {@inheritDoc}
     */
    public function isPrivateUse()
    {
        return $this->inRange(new static('10.0.0.0'), 8)
            || $this->inRange(new static('172.16.0.0'), 12)
            || $this->inRange(new static('192.168.0.0'), 16);
    }

    /**
     * {@inheritDoc}
     */
    public function isUnspecified()
    {
        return $this->getBinary() === "\0\0\0\0";
    }
}
