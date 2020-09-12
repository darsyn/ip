<?php declare(strict_types=1);

namespace Darsyn\IP;

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
     * @param integer $cidr
     * @param integer $length
     * @throws \Darsyn\IP\Exception\InvalidCidrException
     * @return string
     */
    protected function generateBinaryMask($cidr, $length)
    {
        if (!\is_int($cidr)  || !\is_int($length)
            || $cidr < 0    || $length < 0
            // CIDR is measured in bits, whilst we're describing the length
            // in bytes.
            || $cidr > $length * 8
        ) {
            throw new Exception\InvalidCidrException($cidr, $length);
        }
        // Since it takes 4 bits per hexadecimal, how many sections of complete
        // 1's do we have (f's)?
        $mask = \str_repeat('f', \floor($cidr / 4));
        // Now we have less than four 1 bits left we need to determine what
        // hexadecimal character should be added next. Of course, we should only
        // add them in there are 1 bits leftover to prevent going over the
        // 128-bit limit.
        if (0 !== $bits = $cidr % 4) {
            // Create a string representation of a 4-bit binary sequence
            // beginning with the amount of leftover 1's.
            $bin = \str_pad(\str_repeat('1', $bits), 4, '0', STR_PAD_RIGHT);
            // Convert that 4-bit binary string into a hexadecimal character,
            // and append it to the mask.
            $mask .= \dechex(\bindec($bin));
        }
        // Fill the rest of the string up with zero's to pad it out to the
        // correct length (one hex character is worth half a byte).
        $mask = \str_pad($mask, $length * 2, '0', STR_PAD_RIGHT);
        // Pack the hexadecimal sequence into a real, 4 or 16-byte binary
        // sequence.
        $mask = Binary::fromHex($mask);
        return $mask;
    }
}
