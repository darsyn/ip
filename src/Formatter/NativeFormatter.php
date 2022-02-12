<?php

namespace Darsyn\IP\Formatter;

use Darsyn\IP\Exception\Formatter\FormatException;
use Darsyn\IP\Util\MbString;

class NativeFormatter implements ProtocolFormatterInterface
{
    /**
     * {@inheritDoc}
     */
    public function ntop($binary)
    {
        if (\is_string($binary)) {
            $length = MbString::getLength($binary);
            if ($length === 16 || $length === 4) {
                return \inet_ntop(\pack('A' . (string) $length, $binary));
            }
        }
        throw new FormatException($binary);
    }

    /**
     * {@inheritDoc}
     */
    public function pton($binary)
    {
        if (\is_string($binary)) {
            if (\filter_var($binary, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
                $sequence = \unpack('a4', \inet_pton($binary));
                return \current($sequence);
            }
            if (\filter_var($binary, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $sequence = \unpack('a16', \inet_pton($binary));
                return \current($sequence);
            }
            $length = MbString::getLength($binary);
            if ($length === 4 || $length === 16) {
                return $binary;
            }
        }
        throw new FormatException($binary);
    }
}
