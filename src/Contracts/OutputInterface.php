<?php

declare(strict_types=1);

namespace Darsyn\IP\Contracts;

/**
 * @experimental
 */
interface OutputInterface
{
    /** Get Binary Representation */
    public function getBinary(): string;

    /** Implement string casting for IP objects. */
    public function __toString(): string;
}
