<?php

namespace Darsyn\IP\Version;

use Darsyn\IP\AbstractIP;
use Darsyn\IP\Exception;
use Darsyn\IP\Formatter\ProtocolFormatterInterface;

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
    public function __construct($ip)
    {
        // If the IP address has been given in protocol notation, convert it to
        // a 16 byte binary sequence.
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $ip = current(unpack('a16', inet_pton($ip)));
        }

        // If the string was not 16 bytes long, then the IP supplied was neither
        // in protocol notation or binary sequence notation. Throw an exception.
        if (!is_string($ip) || $this->getBinaryLength($ip) !== 16) {
            throw new Exception\WrongVersionException(6, null, $ip);
        }

        parent::__construct($ip);
    }

    /**
     * {@inheritDoc}
     */
    public function getExpandedAddress()
    {
        // Convert the 16-byte binary sequence into a hexadecimal-string
        // representation, insert a colon between every block of 4 characters,
        // and return the resulting IP address in full IPv6 protocol notation.
        return substr(preg_replace('/([a-fA-F0-9]{4})/', '$1:', bin2hex($this->getBinary())), 0, -1);
    }

    /**
     * {@inheritDoc}
     */
    public function getCompactedAddress()
    {
        try {
            return self::getProtocolFormatter()->format($this->getBinary());
        } catch (Exception\Formatter\FormatException $e) {
            throw new Exception\IpException('An unknown error occured internally.', null, $e);
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
        return $this->inRange(new static('fe80::'), 10);
    }

    /**
     * {@inheritDoc}
     */
    public function isLoopback()
    {
        return $this->inRange(new static('::1'), 128);
    }

    /**
     * {@inheritDoc}
     */
    public function isMulticast()
    {
        return $this->inRange(new static('ff00::'), 8);
    }

    /**
     * {@inheritDoc}
     */
    public function isPrivateUse()
    {
        return $this->inRange(new static('fd00::'), 8);
    }

    /**
     * {@inheritDoc}
     */
    public function isUnspecified()
    {
        return $this->getBinary() === "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
    }
}
