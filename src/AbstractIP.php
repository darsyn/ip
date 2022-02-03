<?php

namespace Darsyn\IP;

use Darsyn\IP\Exception\WrongVersionException;
use Darsyn\IP\Formatter\ConsistentFormatter;
use Darsyn\IP\Formatter\ProtocolFormatterInterface;

abstract class AbstractIP implements IpInterface
{
    /** @var \Darsyn\IP\Formatter\ProtocolFormatterInterface $formatter */
    protected static $formatter;

    /**
     * Keep this private to prevent modification of object's main value from
     * child classes.
     * @var string $ip
     */
    private $ip;

    /**
     * @static
     * @param \Darsyn\IP\Formatter\ProtocolFormatterInterface $formatter
     * @return void
     */
    public static function setProtocolFormatter(ProtocolFormatterInterface $formatter)
    {
        self::$formatter = $formatter;
    }

    /**
     * Get the protocol formatter set by the user, falling back to using our
     * custom formatter for consistency by default if the user has not set one
     * globally.
     *
     * @return \Darsyn\IP\Formatter\ProtocolFormatterInterface
     */
    protected static function getProtocolFormatter()
    {
        if (null === self::$formatter) {
            self::$formatter = new ConsistentFormatter;
        }
        return self::$formatter;
    }

    /**
     * Constructor
     *
     * @param string $ip
     */
    protected function __construct($ip)
    {
        $this->ip = $ip;
    }

    /**
     * {@inheritDoc}
     */
    final public function getBinary()
    {
        return $this->ip;
    }

    /**
     * {@inheritDoc}
     */
    public function isVersion($version)
    {
        return $this->getVersion() === $version;
    }

    /**
     * {@inheritDoc}
     */
    public function isVersion4()
    {
        return $this->isVersion(4);
    }

    /**
     * {@inheritDoc}
     */
    public function isVersion6()
    {
        return $this->isVersion(6);
    }

    /**
     * {@inheritDoc}
     */
    public function getNetworkIp($cidr)
    {
        // Providing that the CIDR is valid, bitwise AND the IP address binary
        // sequence with the mask generated from the CIDR.
        return new static($this->getBinary() & $this->generateBinaryMask(
            $cidr,
            Binary::getLength($this->getBinary())
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getBroadcastIp($cidr)
    {
        // Providing that the CIDR is valid, bitwise OR the IP address binary
        // sequence with the inverse of the mask generated from the CIDR.
        return new static($this->getBinary() | ~$this->generateBinaryMask(
            $cidr,
            Binary::getLength($this->getBinary())
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function inRange(IpInterface $ip, $cidr)
    {
        try {
            return $this->getNetworkIp($cidr)->getBinary() === $ip->getNetworkIp($cidr)->getBinary();
        } catch (Exception\InvalidCidrException $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getCommonCidr(IpInterface $ip)
    {
        if ($this->getVersion() !== $ip->getVersion()
            || Binary::getLength($this->getBinary()) !== Binary::getLength($ip->getBinary())
        ) {
            // Cannot calculate the greatest common CIDR between an IPv4 and IPv6 address, they are fundamentally
            // incompatible. Furthermore, the greatest common CIDR cannot be calculated between an IPv4 address and an
            // IPv4 address embedded into an IPv6 address.
            throw new WrongVersionException($this->getVersion(), $ip->getVersion(), $ip);
        }
        $mask = $this->getBinary() ^ $ip->getBinary();
        $parts = explode('1', Binary::toHumanReadable($mask), 2);
        return Binary::getLength($parts[0]);
    }

    /**
     * {@inheritDoc}
     */
    public function isMapped()
    {
        return (new Strategy\Mapped)->isEmbedded($this->getBinary());
    }

    /**
     * {@inheritDoc}
     */
    public function isDerived()
    {
        return (new Strategy\Derived)->isEmbedded($this->getBinary());
    }

    /**
     * {@inheritDoc}
     */
    public function isCompatible()
    {
        return (new Strategy\Compatible)->isEmbedded($this->getBinary());
    }

    /**
     * {@inheritDoc}
     */
    public function isEmbedded()
    {
        return false;
    }

    /**
     * 128-bit masks can often evaluate to integers over PHP_MAX_INT, so we have
     * to construct the bitmask as a string instead of doing any mathematical
     * operations (such as base_convert).
     *
     * @param int $cidr
     * @param int $lengthInBytes
     * @throws \Darsyn\IP\Exception\InvalidCidrException
     * @return string
     */
    protected function generateBinaryMask($cidr, $lengthInBytes)
    {
        if (!\is_int($cidr) || !\is_int($lengthInBytes)
            || $cidr < 0    || $lengthInBytes < 0
            // CIDR is measured in bits, whilst we're describing the length
            // in bytes.
            || $cidr > $lengthInBytes * 8
        ) {
            throw new Exception\InvalidCidrException($cidr, $lengthInBytes);
        }
        // Eg, a CIDR of 24 and length of 4 bytes (IPv4) would make a mask of: 11111111111111111111111100000000.
        $asciiBinarySequence = \str_pad(\str_repeat('1', $cidr), $lengthInBytes * 8, '0', \STR_PAD_RIGHT);
        return Binary::fromHumanReadable($asciiBinarySequence);
    }
}
