<?php

declare(strict_types=1);

namespace Darsyn\IP\Contracts;

/**
 * @experimental
 */
interface VersionIdentityInterface
{
    /** Get the IP version from the binary value */
    public function getVersion(): int;

    /** Is Version? */
    public function isVersion(int $version): bool;

    /** Whether the IP is version 4 */
    public function isVersion4(): bool;

    /** Whether the IP is version 6 */
    public function isVersion6(): bool;
}
