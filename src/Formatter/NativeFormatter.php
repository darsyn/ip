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
                $pack = \pack(\sprintf('A%d', $length), $binary);
                // $pack return type is `string|false` below PHP 8 and `string`
                // above PHP 8.
                // @phpstan-ignore identical.alwaysFalse
                if (false === $pack || false === $protocol = \inet_ntop($pack)) {
                    throw new FormatException($binary);
                }
                return $protocol;
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
                $number = \inet_pton($binary);
                if (false === $number
                    || false === ($sequence = \unpack('a4', $number))
                    || !is_string($return = \current($sequence))
                ) {
                    throw new FormatException($binary);
                }
                return $return;
            }
            if (\filter_var($binary, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $number = \inet_pton($binary);
                if (false === $number
                    || false === ($sequence = \unpack('a16', $number))
                    || !is_string($return = \current($sequence))
                ) {
                    throw new FormatException($binary);
                }
                return $return;
            }
            $length = MbString::getLength($binary);
            if ($length === 4 || $length === 16) {
                return $binary;
            }
        }
        throw new FormatException($binary);
    }
}
