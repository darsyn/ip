<?php declare(strict_types=1);

namespace Darsyn\IP\Formatter;

use Darsyn\IP\Binary;
use Darsyn\IP\Exception\Formatter\FormatException;

class NativeFormatter implements ProtocolFormatterInterface
{
    /** {@inheritdoc} */
    public function ntop(string $binary): string
    {
        if (\is_string($binary)) {
            $length = Binary::getLength($binary);
            if ($length === 16 || $length === 4) {
                $protocol = \inet_ntop(\pack('A' . (string) $length, $binary));
                if (\is_string($protocol)) {
                    return $protocol;
                }
            }
        }
        throw new FormatException($binary);
    }

    /** {@inheritdoc} */
    public function pton(string $protocol): string
    {
        if (\is_string($protocol)) {
            if (\filter_var($protocol, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
                $sequence = \unpack('a4', \inet_pton($protocol));
                return \current($sequence);
            }
            if (\filter_var($protocol, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $sequence = \unpack('a16', \inet_pton($protocol));
                return \current($sequence);
            }
            $length = Binary::getLength($protocol);
            if ($length === 4 || $length === 16) {
                return $protocol;
            }
        }
        throw new FormatException($protocol);
    }
}
