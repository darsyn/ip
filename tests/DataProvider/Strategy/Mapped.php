<?php declare(strict_types=1);

namespace Darsyn\IP\Tests\DataProvider\Strategy;

class Mapped
{
    public static function getValidIpAddresses(): array
    {
        $valid = array_map(function (array $row) {
            $row[1] = true;
            return $row;
        }, self::getValidSequences());
        $invalid = array_map(function (array $row) {
            $row[1] = false;
            return $row;
        }, self::getInvalidSequences());
        return array_merge($valid, $invalid);
    }

    public static function getInvalidIpAddresses(): array
    {
        return [
            [pack('H*', '20010db8000000000a608a2e037073')],
            [pack('H*', '20010db8000000000a608a2e0370734556')],
            ['12345678901234567'],
            ['123456789012345'],
        ];
    }

    public static function getValidSequences(): array
    {
        return [
            [pack('H*', '00000000000000000000ffff00010000'), pack('H*', '00010000')],
            [pack('H*', '00000000000000000000ffff7f000001'), pack('H*', '7f000001')],
            [pack('H*', '00000000000000000000ffff12345678'), pack('H*', '12345678')],
            [pack('H*', '00000000000000000000ffff7f00a001'), pack('H*', '7f00a001')],
        ];
    }

    public static function getInvalidSequences(): array
    {
        return [
            [pack('H*', '000000000000000000000fff00010000')],
            [pack('H*', '00010000000000000000ffff0b120cab')],
            [pack('H*', '000000000000000000000fff7f000001')],
            [pack('H*', '000a0000000000000000ffff7f000001')],
            [pack('H*', '20010db8000000000a608a2e03707334')],
        ];
    }
}
