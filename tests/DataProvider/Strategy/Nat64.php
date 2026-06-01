<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\DataProvider\Strategy;

class Nat64
{
    /** @return list<array{string, bool}> */
    public static function getValidIpAddresses()
    {
        $valid = array_map(static function (array $row) {
            $row[1] = true;
            return $row;
        }, self::getValidSequences());
        $invalid = array_map(static function (array $row) {
            $row[1] = false;
            return $row;
        }, self::getInvalidSequences());
        return array_merge($valid, $invalid);
    }

    /** @return list<array{string}> */
    public static function getInvalidIpAddresses()
    {
        return [
            [pack('H*', '20010db8000000000a608a2e037073')],
            [pack('H*', '20010db8000000000a608a2e0370734556')],
            ['12345678901234567'],
            ['123456789012345'],
        ];
    }

    /** @return list<array{string, string}> */
    public static function getValidSequences()
    {
        return [
            [pack('H*', '0064ff9b000000000000000000010000'), pack('H*', '00010000')],
            [pack('H*', '0064ff9b00000000000000007f000001'), pack('H*', '7f000001')],
            [pack('H*', '0064ff9b000000000000000012345678'), pack('H*', '12345678')],
            [pack('H*', '0064ff9b00000000000000007f00a001'), pack('H*', '7f00a001')],
        ];
    }

    /** @return list<array{string}> */
    public static function getInvalidSequences()
    {
        return [
            [pack('H*', '000000000000000000000fff00010000')],
            [pack('H*', '00010000000000000000ffff0b120cab')],
            [pack('H*', '0064ff9c00000000000000007f000001')],
            [pack('H*', '00000000000000000000ffff7f00a001')],
            [pack('H*', '20010db8000000000a608a2e03707334')],
        ];
    }
}
