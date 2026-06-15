<?php

declare(strict_types=1);

namespace Darsyn\IP\Contracts;

/**
 * @experimental
 */
interface Classification4Interface extends ClassificationInterface
{
    /**
     * Whether the IP is a broadcast address, according to RFC 919 § 7.
     *
     * @throws \Darsyn\IP\Exception\WrongVersionException
     */
    public function isBroadcast(): bool;

    /**
     * Whether the IP is part of the Shared Address Space, according to RFC 6598.
     *
     * @throws \Darsyn\IP\Exception\WrongVersionException
     */
    public function isShared(): bool;

    /**
     * Whether the IP is reserved for future use, according to RFC 1112 § 4
     * (excluding the limited broadcast address, which is a separate
     * special-purpose registry entry per RFC 8190).
     *
     * @throws \Darsyn\IP\Exception\WrongVersionException
     */
    public function isFutureReserved(): bool;
}
