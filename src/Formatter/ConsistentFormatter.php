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
                return $this->ntopVersion6($binary);
            }
            if ($length === 4) {
                return $this->ntopVersion4($binary);
            }
        }
        throw new FormatException($binary);
    }

    /**
     * @param string $binary
     * @return string
     */
    private function ntopVersion6($binary)
    {
        $hex = Binary::toHex($binary);
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
        if (null === $shortened = \preg_replace('/\:{2,}/', '::', \implode(':', $parts))) {
            throw new FormatException($binary);
        }
        return \str_pad($shortened, 2, ':');
    }

    /**
     * @param string $binary
     * @return string
     */
    private function ntopVersion4($binary)
    {
        // $pack return type is `string|false` below PHP 8 and `string`
        // above PHP 8.
        $pack = \pack('A4', $binary);
        // @phpstan-ignore identical.alwaysFalse
        if (false === $pack || false === $protocol = \inet_ntop($pack)) {
            throw new FormatException($binary);
        }
        return $protocol;
    }
}
