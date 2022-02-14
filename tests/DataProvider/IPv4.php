<?php

namespace Darsyn\IP\Tests\DataProvider;

class IPv4 implements IpDataProviderInterface
{
    public static function getValidBinarySequences()
    {
        return [
            [pack('H*', '71637a89'), '71637a89', '113.99.122.137' ],
            [pack('H*', '4708d36c'), '4708d36c', '71.8.211.108'   ],
            [pack('H*', 'c8fa3d9b'), 'c8fa3d9b', '200.250.61.155' ],
            [pack('H*', 'db37478d'), 'db37478d', '219.55.71.141'  ],
            [pack('H*', 'ae823cc4'), 'ae823cc4', '174.130.60.196' ],
            [pack('H*', '0c0679fc'), '0c0679fc', '12.6.121.252'   ],
            [pack('H*', 'ffffffff'), 'ffffffff', '255.255.255.255'],
            ['abcd',                 '61626364', '97.98.99.100'   ],
            ['4d::',                 '34643a3a', '52.100.58.58'   ],
            // Test for null-bytes.
            [pack('H*', '00000000'), '00000000', '0.0.0.0'        ],
            [pack('H*', '00000001'), '00000001', '0.0.0.1'        ],
            [pack('H*', '10000000'), '10000000', '16.0.0.0'       ],
        ];
    }

    public static function getValidProtocolIpAddresses()
    {
        return [
            ['119.14.113.44',   '770e712c', '119.14.113.44',   ],
            ['83.197.36.73',    '53c52449', '83.197.36.73',    ],
            ['18.118.59.40',    '12763b28', '18.118.59.40',    ],
            ['100.39.68.128',   '64274480', '100.39.68.128',   ],
            ['68.192.97.34',    '44c06122', '68.192.97.34',    ],
            ['141.216.7.75',    '8dd8074b', '141.216.7.75',    ],
            ['151.197.48.205',  '97c530cd', '151.197.48.205',  ],
            ['182.234.197.141', 'b6eac58d', '182.234.197.141', ],
            // Test for null-bytes.
            ['0.0.0.0',         '00000000', '0.0.0.0'          ],
            ['0.0.0.1',         '00000001', '0.0.0.1'          ],
            ['16.0.0.0',        '10000000', '16.0.0.0'         ],
        ];
    }

    public static function getValidIpAddresses()
    {
        return array_merge(self::getValidBinarySequences(), self::getValidProtocolIpAddresses());
    }

    public static function getInvalidIpAddresses()
    {
        return [
            ['::1'],
            ['12.34.567.89'],
            ['2001:db8::a60:8a2e:370g:7334'],
            ['1.2.3'],
            ['This one is completely wrong.'],
            // 5 bytes instead of 4.
            [pack('H*', '20010db80')],
            [123],
            [1.3],
            [array()],
            [(object) array()],
            [null],
            [true],
            ['12345'],
            ['123'],
        ];
    }

    public static function getValidCidrValues()
    {
        return [
            [32, 'ffffffff'],
            [16, 'ffff0000'],
            [17, 'ffff8000'],
            [1,  '80000000'],
            [2,  'c0000000'],
            [3,  'e0000000'],
            [4,  'f0000000'],
            [5,  'f8000000'],
            [0,  '00000000'],
        ];
    }

    public static function getInvalidCidrValues()
    {
        return [
            [-1],
            [33],
            ['0'],
            ['128'],
            [12.3],
            [true],
            [null],
            [[]],
            [(object) []],
        ];
    }

    public static function getNetworkIpAddresses()
    {
        return [
            ['12.34.56.78', 32],
            ['12.34.56.0',  24],
            ['12.34.48.0',  20],
            ['12.34.0.0',   16],
            ['12.32.0.0',   13],
            ['12.0.0.0',    8 ],
        ];
    }

    public static function getBroadcastIpAddresses()
    {
        return [
            ['12.34.56.78',     32],
            ['12.34.56.255',    24],
            ['12.34.63.255',    20],
            ['12.34.255.255',   16],
            ['12.39.255.255',   13],
            ['12.255.255.255',  8 ],
        ];
    }

    public static function getValidInRangeIpAddresses()
    {
        return [
            ['12.34.56.78',     '12.34.56.78',      32],
            ['0.0.0.1',         '255.255.255.254',  0 ],
            ['12.34.143.96',    '12.34.201.26',     16],
            ['12.34.255.252',   '12.34.255.255',    30],
        ];
    }

    public static function getLinkLocalIpAddresses()
    {
        return [
            ['169.253.255.255', false],
            ['169.254.0.0',     true ],
            ['169.254.255.255', true ],
            ['169.255.0.0',     false],
        ];
    }

    public static function getLoopbackIpAddresses()
    {
        return [
            ['126.255.255.255', false],
            ['127.0.0.0',       true ],
            ['127.255.255.255', true ],
            ['128.0.0.0',       false],
        ];
    }

    public static function getMulticastIpAddresses()
    {
        return [
            ['223.255.255.255', false],
            ['224.0.0.0',       true ],
            ['239.255.255.255', true ],
            ['240.0.0.0',       false],
        ];
    }

    public static function getPrivateUseIpAddresses()
    {
        return [
            ['9.255.255.255',   false],
            ['10.0.0.0',        true ],
            ['10.255.255.255',  true ],
            ['11.0.0.0',        false],
            ['172.15.255.255',  false],
            ['172.16.0.0',      true ],
            ['172.31.255.255',  true ],
            ['172.32.0.0',      false],
            ['192.167.255.255', false],
            ['192.168.0.0',     true ],
            ['192.168.255.255', true ],
            ['192.169.0.0',     false],
        ];
    }

    public static function getUnspecifiedIpAddresses()
    {
        return [
            ['0.0.0.0',   true ],
            ['0.0.0.1',   false],
            ['127.0.0.1', false],
        ];
    }

    /** {@inheritDoc} */
    public static function getCategorizedIpAddresses()
    {
        return [];
    }

    /** {@inheritDoc} */
    public static function getCategoryOfIpAddresses($category)
    {
        $data = [];
        $true = $false = 0;
        foreach (self::getCategorizedIpAddresses() as $ipAddress => $categories) {
            if (($categories & $category) > 0) {
                $data[] = [$ipAddress, true];
                $true++;
            } else {
                $data[] = [$ipAddress, false];
                $false++;
            }
        }
        if ($true === 0 || $false === 0) {
            throw new \DomainException('Please supply both valid and invalid IP addresses for test.');
        }
        return $data;
    }
}
