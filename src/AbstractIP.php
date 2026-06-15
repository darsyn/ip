<?php

declare(strict_types=1);

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

    public static function setProtocolFormatter(ProtocolFormatterInterface $formatter): void
    {
        self::$formatter = $formatter;
    }

    /**
     * Get the protocol formatter set by the user, falling back to using our
     * custom formatter for consistency by default if the user has not set one
     * globally.
     */
    protected static function getProtocolFormatter(): ProtocolFormatterInterface
    {
        if (null === self::$formatter) {
            self::$formatter = new ConsistentFormatter();
        }
        return self::$formatter;
    }

    /**
     * Resolve the formatter for a single formatting call.
     * Passing a non-null argument that is not a ProtocolFormatterInterface is
     * deprecated; the global formatter is used instead.
     *
     * @param list<mixed> $arguments The calling method's func_get_args().
     */
    protected static function resolveProtocolFormatter(array $arguments): ProtocolFormatterInterface
    {
        $formatter = $arguments[0] ?? null;
        if (null === $formatter) {
            return self::getProtocolFormatter();
        }
        if (!$formatter instanceof ProtocolFormatterInterface) {
            \trigger_error(\sprintf(
                'Passing a non-null value that is not an instance of %s to a formatting method is deprecated; %s given. The global formatter was used instead.',
                ProtocolFormatterInterface::class,
                \is_object($formatter) ? \get_class($formatter) : \gettype($formatter)
            ), \E_USER_DEPRECATED);
            return self::getProtocolFormatter();
        }
        return $formatter;
    }

    protected function __construct(string $ip)
    {
        $this->ip = $ip;
    }

    final public function getBinary(): string
    {
        return $this->ip;
    }

    public function equals(IpInterface $ip): bool
    {
        return $this->getBinary() === $ip->getBinary();
    }

    public function isVersion(int $version): bool
    {
        return $this->getVersion() === $version;
    }

    public function isVersion4(): bool
    {
        return $this->isVersion(4);
    }

    public function isVersion6(): bool
    {
        return $this->isVersion(6);
    }

    public function getNetworkIp(int $cidr)
    {
        // Providing that the CIDR is valid, bitwise AND the IP address binary
        // sequence with the mask generated from the CIDR.
        return new static($this->getBinary() & Binary::mask(
            $cidr,
            MbString::getLength($this->getBinary())
        ));
    }

    public function getBroadcastIp(int $cidr)
    {
        // Providing that the CIDR is valid, bitwise OR the IP address binary
        // sequence with the inverse of the mask generated from the CIDR.
        return new static($this->getBinary() | ~Binary::mask(
            $cidr,
            MbString::getLength($this->getBinary())
        ));
    }

    public function inRange(IpInterface $ip, int $cidr): bool
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

    public function getCommonCidr(IpInterface $ip): int
    {
        // Cannot calculate the greatest common CIDR between an IPv4 and
        // IPv6/IPv4-embedded address, they are fundamentally incompatible.
        if (!$this->isSameByteLength($ip)) {
            throw new WrongVersionException(
                4 === MbString::getLength($this->getBinary()) ? 4 : 6,
                4 === MbString::getLength($ip->getBinary()) ? 4 : 6,
                (string) $ip
            );
        }
        // The greatest common CIDR is the number of leading zero bits in the
        // XOR of the two addresses.
        $xor = $this->getBinary() ^ $ip->getBinary();
        $commonBytes = \strspn($xor, "\x00");
        $commonCidr = $commonBytes * 8;
        if ($commonBytes < MbString::getLength($xor)) {
            $commonCidr += 8 - MbString::getLength(\decbin(\ord($xor[$commonBytes])));
        }
        return $commonCidr;
    }

    public function isMapped(): bool
    {
        return (new Strategy\Mapped())->isEmbedded($this->getBinary());
    }

    public function isDerived(): bool
    {
        return (new Strategy\Derived())->isEmbedded($this->getBinary());
    }

    public function isCompatible(): bool
    {
        return (new Strategy\Compatible())->isEmbedded($this->getBinary());
    }

    public function isEmbedded(): bool
    {
        return false;
    }

    protected function isSameByteLength(IpInterface $ip): bool
    {
        return MbString::getLength($this->getBinary()) === MbString::getLength($ip->getBinary());
    }
}
