<?php

namespace Darsyn\IP\Formatter;

use Darsyn\IP\Exception\Formatter\FormatException;

class ConsistentFormatter implements ProtocolFormatterInterface
{
    /**
     * {@inheritDoc}
     */
    public function format($ip)
    {
        if (is_string($ip)) {
            $hex = bin2hex($ip);
            $length = strlen($hex) / 2;
            if ($length === 16) {
                return $this->formatVersion6($hex);
            }
            if ($length === 4) {
                return $this->formatVersion4($hex);
            }
        }
        throw new FormatException($ip);
    }

    private function formatVersion6($hex)
    {
        $parts = str_split($hex, 4);
        $zeroes = array_map(function ($part) {
            return $part === '0000';
        }, $parts);
        $length = $i = 0;
        $sequences = [];
        foreach ($zeroes as $zero) {
            $length = $zero ? ++$length : 0;
            $sequences[++$i] = $length;
        }
        $maxLength = max($sequences);
        $position = array_search($maxLength, $sequences, true) - $maxLength;
        $parts = array_map(function ($part) {
            return ltrim($part, '0') ?: '0';
        }, $parts);
        if ($maxLength > 0) {
            array_splice($parts, $position, $maxLength, ':');
        }
        return str_pad(preg_replace('/\:{2,}/', '::', implode(':', $parts)), 2, ':');
    }

    private function formatVersion4($hex)
    {
        return implode('.', array_map(function ($hex) {
            return (string) hexdec($hex);
        }, str_split($hex, 2)));
    }
}
