<?php

namespace Darsyn\IP\Formatter;

use Darsyn\IP\Exception\Formatter\FormatException;

class NativeFormatter implements ProtocolFormatterInterface
{
    /**
     * {@inheritDoc}
     */
    public function ntop($binary)
    {
        if (is_string($binary)) {
            $length = strlen(bin2hex($binary)) / 2;
            if ($length === 16 || $length === 4) {
                return inet_ntop(pack('A' . (string) $length, $binary));
            }
        }
        throw new FormatException($binary);
    }

    /**
     * {@inheritDoc}
     */
    public function pton($protocol)
    {
        if (is_string($protocol)) {
            if (filter_var($protocol, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $sequence = unpack('a4', inet_pton($protocol));
                return current($sequence);
            } elseif (filter_var($protocol, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $sequence = unpack('a16', inet_pton($protocol));
                return current($sequence);
            }
            $length = strlen(bin2hex($protocol)) / 2;
            if ($length === 4 || $length === 16) {
                return $protocol;
            }
        }
        throw new FormatException($protocol);
    }
}
