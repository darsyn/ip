<?php declare(strict_types=1);

namespace Darsyn\IP\Version;

use Darsyn\IP\AbstractIP;
use Darsyn\IP\Binary;
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
     * @inheritDoc
     * @return \Darsyn\IP\Version\IPv4
     */
    public static function factory(string $ip)
    {
        try {
            // Convert from protocol notation to binary sequence.
            $binary = self::getProtocolFormatter()->pton($ip);
            // If the string was not 4 bytes long, then the IP supplied was
            // neither in protocol notation or binary sequence notation. Throw
            // an exception.
            if (Binary::getLength($binary) !== 4) {
                if (Binary::getLength($ip) !== 4) {
                    throw new Exception\WrongVersionException(4, 6, $ip);
                }
                $binary = $ip;
            }
        } catch(Exception\IpException $e) {
            throw new Exception\InvalidIpAddressException($ip, $e);
        }
        return new static($binary);
    }

    /** @inheritDoc */
    public function getDotAddress(): string
    {
        try {
            return self::getProtocolFormatter()->ntop($this->getBinary());
        } catch (Exception\Formatter\FormatException $e) {
            throw new Exception\IpException('An unknown error occured internally.', null, $e);
        }
    }

    /** @inheritDoc */
    public function getVersion(): int
    {
        return 4;
    }

    /**
     * @inheritDoc
     * @return \Darsyn\IP\Version\IPv4
     */
    public function getNetworkIp(int $cidr)
    {
        return parent::getNetworkIp($cidr);
    }

    /**
     * @inheritDoc
     * @return \Darsyn\IP\Version\IpV4
     */
    public function getBroadcastIp(int $cidr)
    {
        return parent::getBroadcastIp($cidr);
    }

    /** @inheritDoc */
    public function isLinkLocal(): bool
    {
        return $this->inRange(new static(Binary::fromHex('a9fe0000')), 16);
    }

    /** @inheritDoc */
    public function isLoopback(): bool
    {
        return $this->inRange(new static(Binary::fromHex('7f000000')), 8);
    }

    /** @inheritDoc */
    public function isMulticast(): bool
    {
        return $this->inRange(new static(Binary::fromHex('e0000000')), 4);
    }

    /** @inheritDoc */
    public function isPrivateUse(): bool
    {
        return $this->inRange(new static(Binary::fromHex('0a000000')), 8)
            || $this->inRange(new static(Binary::fromHex('ac100000')), 12)
            || $this->inRange(new static(Binary::fromHex('c0a80000')), 16);
    }

    /** @inheritDoc */
    public function isUnspecified(): bool
    {
        return $this->getBinary() === "\0\0\0\0";
    }
}
