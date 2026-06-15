<?php

declare(strict_types=1);

namespace Darsyn\IP\Contracts;

/**
 * @experimental
 */
interface Classification6Interface extends ClassificationInterface
{
    // Multicast scope field values, as defined by RFC 7346 § 2 (which updated
    // the original RFC 4291 § 2.7 table; realm-local scope 3 is only defined
    // in RFC 7346).
    public const MULTICAST_INTERFACE_LOCAL = 1;
    public const MULTICAST_LINK_LOCAL = 2;
    public const MULTICAST_REALM_LOCAL = 3;
    public const MULTICAST_ADMIN_LOCAL = 4;
    public const MULTICAST_SITE_LOCAL = 5;
    public const MULTICAST_ORGANIZATION_LOCAL = 8;
    public const MULTICAST_GLOBAL = 14;

    /**
     * Returns the IP address’s multicast scope if the address is multicast,
     * null otherwise. Return values are integers mapped to the MULTICAST_*
     * constants on this interface, with scope values defined by RFC 7346 § 2.
     */
    public function getMulticastScope(): ?int;

    /** Whether the IP is a unique local address, according to RFC 4193. */
    public function isUniqueLocal(): bool;

    /** Whether the IP is a unicast address, according to RFC 4291. */
    public function isUnicast(): bool;

    /**
     * Whether the IP is a globally routable unicast address, according to
     * RFC 4291 § 2.5.4.
     */
    public function isUnicastGlobal(): bool;
}
