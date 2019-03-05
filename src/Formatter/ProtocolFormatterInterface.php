<?php declare(strict_types=1);

namespace Darsyn\IP\Formatter;

interface ProtocolFormatterInterface
{
    /**
     * Protocol to Binary
     *
     * @throws \Darsyn\IP\Exception\Formatter\FormatException
     */
    public function pton(string $binary): string;

    /**
     * Binary to Protocol
     *
     * @throws \Darsyn\IP\Exception\Formatter\FormatException
     */
    public function ntop(string $binary): string;
}
