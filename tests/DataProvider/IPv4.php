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

    public static function getCommonCidrValues()
    {
        return [
            ['44.245.50.26',    '56.114.0.41',       3],
            ['200.141.21.5',    '200.141.135.157',  16],
            ['237.129.110.166', '237.129.100.102',  20],
            ['214.37.48.96',    '192.63.84.23',      3],
            ['62.89.145.8',     '62.89.148.123',    21],
            ['155.52.27.103',   '155.52.58.70',     18],
            ['197.250.178.207', '197.249.253.234',  14],
            ['59.150.252.194',  '59.148.136.197',   14],
            ['184.15.189.34',   '184.15.189.47',    28],
            ['253.220.86.36',   '253.224.132.32',   10],
            ['210.119.73.9',    '210.119.118.212',  18],
            ['43.89.127.34',    '43.68.154.30',     11],
            ['239.90.58.123',   '239.90.58.121',    30],
            ['68.246.90.236',   '68.245.82.136',    14],
            ['96.154.140.157',  '96.198.75.94',      9],
            ['203.147.238.101', '203.147.248.131',  19],
        ];
    }

    public static function getLinkLocalIpAddresses()
    {
        return self::getCategoryOfIpAddresses(self::LINK_LOCAL);
    }

    public static function getLoopbackIpAddresses()
    {
        return self::getCategoryOfIpAddresses(self::LOOPBACK);
    }

    public static function getMulticastIpAddresses()
    {
        return self::getCategoryOfIpAddresses(self::MULTICAST);
    }

    public static function getPrivateUseIpAddresses()
    {
        return self::getCategoryOfIpAddresses(self::PRIVATE_USE);
    }

    public static function getUnspecifiedIpAddresses()
    {
        return self::getCategoryOfIpAddresses(self::UNSPECIFIED);
    }

    public static function getBenchmarkingIpAddresses()
    {
        return self::getCategoryOfIpAddresses(self::BENCHMARKING);
    }

    public static function getDocumentationIpAddresses()
    {
        return self::getCategoryOfIpAddresses(self::DOCUMENTATION);
    }

    public static function getPublicUseIpAddresses()
    {
        return self::getCategoryOfIpAddresses(self::PUBLIC_USE_V4);
    }

    public static function getIsBroadcastIpAddresses()
    {
        return self::getCategoryOfIpAddresses(self::BROADCAST);
    }

    public static function getSharedIpAddresses()
    {
        return self::getCategoryOfIpAddresses(self::SHARED);
    }

    public static function getFutureReservedIpAddresses()
    {
        return self::getCategoryOfIpAddresses(self::FUTURE_RESERVED);
    }

    /** {@inheritDoc} */
    public static function getCategorizedIpAddresses()
    {
        return [
            '10.9.8.7' => self::PRIVATE_USE,
            '127.1.2.3' => self::LOOPBACK,
            '172.31.254.253' => self::PRIVATE_USE,
            '169.254.253.242' => self::LINK_LOCAL,
            '192.0.2.183' => self::DOCUMENTATION,
            '192.1.2.183' => self::PUBLIC_USE,
            '192.168.254.253' => self::PRIVATE_USE,
            '198.51.100.0' => self::DOCUMENTATION,
            '203.0.113.0' => self::DOCUMENTATION,
            '203.2.113.0' => self::PUBLIC_USE,
            '255.255.255.255' => self::BROADCAST,
            '198.18.0.0' => self::BENCHMARKING,
            '198.18.54.2' => self::BENCHMARKING,
            '224.0.0.0' => self::PUBLIC_USE | self::MULTICAST_IPV4,
            '239.255.255.255' => self::PUBLIC_USE | self::MULTICAST_IPV4,
            '0.0.0.0' => self::UNSPECIFIED,
            '10.0.0.0' => self::PRIVATE_USE,
            '10.255.255.255' => self::PRIVATE_USE,
            '172.16.0.0' => self::PRIVATE_USE,
            '172.31.255.255' => self::PRIVATE_USE,
            '192.168.0.0' => self::PRIVATE_USE,
            '192.168.255.255' => self::PRIVATE_USE,
            '127.0.0.0' => self::LOOPBACK,
            '127.255.255.255' => self::LOOPBACK,
            '169.254.0.0' => self::LINK_LOCAL,
            '169.254.255.255' => self::LINK_LOCAL,
            '100.64.91.200' => self::SHARED,
            '251.0.12.101' => self::FUTURE_RESERVED,
            '192.18.0.0' => self::PUBLIC_USE,
            '198.19.255.255' => self::BENCHMARKING,
            '129.129.154.203' => self::PUBLIC_USE,
            '239.248.153.114' => self::PUBLIC_USE | self::MULTICAST_IPV4,
            '85.101.159.135' => self::PUBLIC_USE,
            '72.64.156.77' => self::PUBLIC_USE,
            '162.199.210.167' => self::PUBLIC_USE,
            '2.12.191.95' => self::PUBLIC_USE,
            '83.125.176.74' => self::PUBLIC_USE,
            '224.0.65.129' => self::PUBLIC_USE | self::MULTICAST_IPV4,
        ];
    }

    /** {@inheritDoc} */
    public static function getCategoryOfIpAddresses($category, $exclude = 0)
    {
        $data = [];
        $true = $false = 0;
        $ipAddresses = self::getCategorizedIpAddresses();
        $ipAddresses = array_filter($ipAddresses, function ($categories) use ($exclude) {
            return !(($categories & $exclude) > 0);
        });
        foreach ($ipAddresses as $ipAddress => $categories) {
            $isIpInCategory = ($categories & $category) > 0;
            $data[] = [$ipAddress, $isIpInCategory];
            $isIpInCategory ? $true++ : $false++;
        }
        if ($true === 0) {
            throw new \DomainException('Test data only contains invalid IP addresses for the test category; supply valid cases too.');
        }
        if ($false === 0) {
            throw new \DomainException('Test data only contains valid IP addresses for the test category; supply invalid cases too.');
        }
        return $data;
    }
}
