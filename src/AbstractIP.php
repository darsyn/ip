<?php

namespace Darsyn\IP;

use Darsyn\IP\Exception\WrongVersionException;
use Darsyn\IP\Formatter\ConsistentFormatter;
use Darsyn\IP\Formatter\ProtocolFormatterInterface;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Util\MbString;

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
    public function equals(IpInterface $ip)
    {
        return $this->getBinary() === $ip->getBinary();
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
            MbString::getLength($this->getBinary())
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
            MbString::getLength($this->getBinary())
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function inRange(IpInterface $ip, $cidr)
    {
        if (!$this->isSameByteLength($ip)) {
            // Cannot calculate if one IP is in range of another if they of different byte-lengths.
            throw new WrongVersionException($this->getVersion(), $ip->getVersion(), (string) $ip);
        }
        // If this method is being called, it means Multi may have failed it's
        // IPv4 check, and we must proceed as IPv6 only. We must perform
        // getNetworkIp() as IPv6, otherwise instances of Multi with IPv4-embedded
        // addresses and CIDR below 32 will return an incorrect network IP for
        // comparison.
        $ours = $this instanceof Version\MultiVersionInterface ? new Version\IPv6($this->getBinary()) : $this;
        $theirs = $ip instanceof Version\MultiVersionInterface ? new Version\IPv6($ip->getBinary()) : $ip;
        return $ours->getNetworkIp($cidr)->getBinary() === $theirs->getNetworkIp($cidr)->getBinary();
    }

    /** {@inheritDoc} */
    public function getCommonCidr(IpInterface $ip)
    {
        // Cannot calculate the greatest common CIDR between an IPv4 and
        // IPv6/IPv4-embedded address, they are fundamentally incompatible.
        if (!$this->isSameByteLength($ip)) {
            throw new WrongVersionException(
                MbString::getLength($this->getBinary()) === 4 ? 4 : 6,
                MbString::getLength($ip->getBinary()) === 4 ? 4 : 6,
                (string) $ip
            );
        }
        $mask = $this->getBinary() ^ $ip->getBinary();
        $parts = explode('1', Binary::toHumanReadable($mask), 2);
        return MbString::getLength($parts[0]);
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
     * @param \Darsyn\IP\IpInterface $ip
     * @return bool
     */
    protected function isSameByteLength(IpInterface $ip)
    {
        return MbString::getLength($this->getBinary()) === MbString::getLength($ip->getBinary());
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
            // CIDR is measured in bits; we're describing the length in bytes.
            || $cidr > $lengthInBytes * 8
        ) {
            throw new Exception\InvalidCidrException($cidr, $lengthInBytes);
        }
        // Eg, a CIDR of 24 and length of 4 bytes (IPv4) would make a mask of:
        // 11111111111111111111111100000000.
        $asciiBinarySequence = MbString::padString(
            \str_repeat('1', $cidr),
            $lengthInBytes * 8,
            '0',
            \STR_PAD_RIGHT
        );
        return Binary::fromHumanReadable($asciiBinarySequence);
    }
}
