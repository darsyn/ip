<?php

namespace Darsyn\IP\Formatter;

use Darsyn\IP\Exception\Formatter\FormatException;

class NativeFormatter implements ProtocolFormatterInterface
{
    /**
     * {@inheritDoc}
     */
    public function format($ip)
    {
        if (is_string($ip)) {
            $length = strlen(bin2hex($ip)) / 2;
            if ($length === 16 || $length === 4) {
                return inet_ntop(pack('A' . (string)$length, $ip));
            }
        }
        throw new FormatException($ip);
    }
}
