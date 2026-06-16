<?php

declare(strict_types=1);

namespace Darsyn\IP;

use Darsyn\IP\Contracts\ArithmeticInterface;
use Darsyn\IP\Contracts\ClassificationInterface;
use Darsyn\IP\Contracts\ComparisonInterface;
use Darsyn\IP\Contracts\OutputInterface;
use Darsyn\IP\Contracts\VersionIdentityInterface;

interface IpInterface extends ArithmeticInterface, ClassificationInterface, ComparisonInterface, OutputInterface, VersionIdentityInterface
{
    /**
     * @throws \Darsyn\IP\Exception\InvalidIpAddressException
     * @throws \Darsyn\IP\Exception\WrongVersionException
     * @return static
     * @deprecated Use fromProtocol() for protocol notation, or fromBinary() for a raw binary sequence.
     */
    public static function factory(string $ip);

    /**
     * Whether the IP is an IPv4-mapped IPv6 address, according to
     * RFC 4291 § 2.5.5.2 (eg, "::ffff:7f00:1").
     */
    public function isMapped(): bool;

    /**
     * Whether the IP is a 6to4-derived address, according to RFC 3056 § 2. Any
     * address within the 6to4 block `2002::/16` (eg, "2002:7f00:1::"), all of
     * which embed an IPv4 address in bits 16-47.
     */
    public function isDerived(): bool;

    /**
     * Whether the IP is an IPv4-compatible IPv6 address, according to
     * RFC 4291 § 2.5.5.1 (eg, `::7f00:1`); deprecated by that same RFC.
     */
    public function isCompatible(): bool;

    /**
     * Whether the IP is an IPv4-embedded IPv6 address (according to the
     * embedding strategy used).
     */
    public function isEmbedded(): bool;

    /**
     * Superseded by `isGloballyReachable()` which conforms to official wording;
     * "Public Use" does not appear in the IANA special-purpose registries.
     *
     * @deprecated Use isGloballyReachable() instead.
     */
    public function isPublicUse(): bool;
}
