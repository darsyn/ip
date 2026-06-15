<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\Stub;

use Darsyn\IP\Formatter\ProtocolFormatterInterface;

class StubFormatter implements ProtocolFormatterInterface
{
    public const SENTINEL = 'stub-formatter-output';

    public function pton(string $binary): string
    {
        return $binary;
    }

    public function ntop(string $binary): string
    {
        return self::SENTINEL;
    }
}
