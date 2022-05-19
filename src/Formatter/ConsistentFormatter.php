<?php

namespace Darsyn\IP\Formatter;

use Darsyn\IP\Exception\Formatter\FormatException;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Util\MbString;

class ConsistentFormatter extends NativeFormatter
{
    /**
     * {@inheritDoc}
     */
    public function ntop($binary)
    {
        if (\is_string($binary)) {
            $length = MbString::getLength($binary);
            if ($length === 16) {
                return $this->ntopVersion6(Binary::toHex($binary));
            }
            if ($length === 4) {
                return $this->ntopVersion4($binary);
            }
        }
        throw new FormatException($binary);
    }

    /**
     * @param string $hex
     * @return string
     */
    private function ntopVersion6($hex)
    {
        $parts = \str_split($hex, 4);
        $zeroes = \array_map(function ($part) {
            return $part === '0000';
        }, $parts);
        $length = $i = 0;
        $sequences = [];
        foreach ($zeroes as $zero) {
            $length = $zero ? ++$length : 0;
            $sequences[++$i] = $length;
        }
        if (\count($sequences) > 0) {
            $maxLength = \max($sequences);
            $endPosition = \array_search($maxLength, $sequences, true);
            if (!\is_int($endPosition)) {
                throw new \RuntimeException;
            }
            $startPosition = $endPosition - $maxLength;
        } else {
            $maxLength = $startPosition = 0;
        }
        $parts = \array_map(function ($part) {
            return \ltrim($part, '0') ?: '0';
        }, $parts);
        if ($maxLength > 0) {
            \array_splice($parts, $startPosition, $maxLength, ':');
        }
        return \str_pad(\preg_replace('/\:{2,}/', '::', \implode(':', $parts)), 2, ':');
    }

    /**
     * @param string $binary
     * @return string
     */
    private function ntopVersion4($binary)
    {
        return \inet_ntop(\pack('A4', $binary));
    }
}
