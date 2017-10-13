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
        $expanded = substr(preg_replace('/([a-fA-F0-9]{4})/', '$1:', $hex), 0, -1);
        return preg_replace(
            '/\:(?:0\:)+/', '::',
            preg_replace_callback('/\:{2,}/', function (array $matches) {
                return implode('0', str_split($matches[0]));
            }, preg_replace('/(?:^0+|(\:)0+)/', '$1', $expanded)),
            1
        );
    }

    private function formatVersion4($hex)
    {
        return implode('.', array_map(function ($hex) {
            return (string) hexdec($hex);
        }, str_split($hex, 2)));
    }
}
