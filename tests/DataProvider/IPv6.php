<?php

namespace Darsyn\IP\Tests\DataProvider;

class IPv6 implements IpDataProviderInterface
{
    public static function getValidBinarySequences()
    {
        return [
            // [ constructor value, expected hex, expected expanded address, expected compacted address ].
            [pack('H*', 'd6be058371a4aa6dc77d77dd0cecf897'), 'd6be058371a4aa6dc77d77dd0cecf897', 'd6be:0583:71a4:aa6d:c77d:77dd:0cec:f897', 'd6be:583:71a4:aa6d:c77d:77dd:cec:f897'  ],
            [pack('H*', '2d7f424dc574632e8d9d847d9f30b62a'), '2d7f424dc574632e8d9d847d9f30b62a', '2d7f:424d:c574:632e:8d9d:847d:9f30:b62a', '2d7f:424d:c574:632e:8d9d:847d:9f30:b62a'],
            [pack('H*', '10d4ebf63401e851b3fd0d78ba5abf44'), '10d4ebf63401e851b3fd0d78ba5abf44', '10d4:ebf6:3401:e851:b3fd:0d78:ba5a:bf44', '10d4:ebf6:3401:e851:b3fd:d78:ba5a:bf44' ],
            [pack('H*', '7bf9a81f7047b07af891a84925c752c8'), '7bf9a81f7047b07af891a84925c752c8', '7bf9:a81f:7047:b07a:f891:a849:25c7:52c8', '7bf9:a81f:7047:b07a:f891:a849:25c7:52c8'],
            [pack('H*', '9800ea8800a5cbcc9d6868f3dc4ace01'), '9800ea8800a5cbcc9d6868f3dc4ace01', '9800:ea88:00a5:cbcc:9d68:68f3:dc4a:ce01', '9800:ea88:a5:cbcc:9d68:68f3:dc4a:ce01'  ],
            [pack('H*', 'c3f889b050c8b06c043cff4f7f4ae66d'), 'c3f889b050c8b06c043cff4f7f4ae66d', 'c3f8:89b0:50c8:b06c:043c:ff4f:7f4a:e66d', 'c3f8:89b0:50c8:b06c:43c:ff4f:7f4a:e66d' ],
            [pack('H*', 'ffffffffffffffffffffffffffffffff'), 'ffffffffffffffffffffffffffffffff', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'],
            ['1234567890123456',                             '31323334353637383930313233343536', '3132:3334:3536:3738:3930:3132:3334:3536', '3132:3334:3536:3738:3930:3132:3334:3536'],
            // Test for null-bytes.
            [pack('H*', '00000000000000000000000000000000'), '00000000000000000000000000000000', '0000:0000:0000:0000:0000:0000:0000:0000', '::'                                     ],
            [pack('H*', '00000000000000000000000000000001'), '00000000000000000000000000000001', '0000:0000:0000:0000:0000:0000:0000:0001', '::1'                                    ],
            [pack('H*', '10000000000000000000000000000000'), '10000000000000000000000000000000', '1000:0000:0000:0000:0000:0000:0000:0000', '1000::'                                 ],
        ];
    }

    public static function getValidProtocolIpAddresses()
    {
        return [
            ['::b12:cab',                       '0000000000000000000000000b120cab', '0000:0000:0000:0000:0000:0000:0b12:0cab', '::b12:cab'                  ],
            ['::12.34.56.78',                   '0000000000000000000000000c22384e', '0000:0000:0000:0000:0000:0000:0c22:384e', '::c22:384e'                 ],
            ['::ffff:0c22:384e',                '00000000000000000000ffff0c22384e', '0000:0000:0000:0000:0000:ffff:0c22:384e', '::ffff:c22:384e'            ],
            ['2002:c22:384e::',                 '20020c22384e00000000000000000000', '2002:0c22:384e:0000:0000:0000:0000:0000', '2002:c22:384e::'            ],
            ['2001:db8::a60:8a2e:370:7334',     '20010db8000000000a608a2e03707334', '2001:0db8:0000:0000:0a60:8a2e:0370:7334', '2001:db8::a60:8a2e:370:7334'],
            ['2001:db8::a60:8a2e:0:7334',       '20010db8000000000a608a2e00007334', '2001:0db8:0000:0000:0a60:8a2e:0000:7334', '2001:db8::a60:8a2e:0:7334'  ],
            ['2001:0db8:0::a60:8a2e:0370:7334', '20010db8000000000a608a2e03707334', '2001:0db8:0000:0000:0a60:8a2e:0370:7334', '2001:db8::a60:8a2e:370:7334'],
            // Test for null-bytes.
            ['::',                              '00000000000000000000000000000000', '0000:0000:0000:0000:0000:0000:0000:0000', '::'                         ],
            ['::1',                             '00000000000000000000000000000001', '0000:0000:0000:0000:0000:0000:0000:0001', '::1'                        ],
            ['1000::',                          '10000000000000000000000000000000', '1000:0000:0000:0000:0000:0000:0000:0000', '1000::'                     ],
        ];
    }

    public static function getValidIpAddresses()
    {
        return array_merge(self::getValidBinarySequences(), self::getValidProtocolIpAddresses());
    }

    public static function getInvalidIpAddresses()
    {
        return [
            ['0.0.0.0'],
            ['255.255.255.255'],
            ['12.34.56.78'],
            ['2001:db8::a60:8a2e:370g:7334'],
            ['1.2.3'],
            ['This one is completely wrong.'],
            // 15 bytes instead of 16.
            [pack('H*', '20010db8000000000a608a2e037073')],
            [123],
            [1.3],
            [array()],
            [(object) array()],
            [null],
            [true],
            ['12345678901234567'],
            ['123456789012345'],
        ];
    }

    public static function getValidCidrValues()
    {
        return [
            [0,   '00000000000000000000000000000000'],
            [128, 'ffffffffffffffffffffffffffffffff'],
            [64,  'ffffffffffffffff0000000000000000'],
            [1,   '80000000000000000000000000000000'],
            [2,   'c0000000000000000000000000000000'],
            [3,   'e0000000000000000000000000000000'],
            [4,   'f0000000000000000000000000000000'],
            [5,   'f8000000000000000000000000000000'],
        ];
    }

    public static function getInvalidCidrValues()
    {
        return [
            [-1],
            [129],
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
            ['2000::',                      12 ],
            ['2001:db8::',                  59 ],
            ['2001:db8:0:0:800::',          70 ],
            ['2001:db8::a60:8a2e:0:0',      99 ],
            ['2001:db8::a60:8a2e:370:7334', 128],
        ];
    }

    public static function getBroadcastIpAddresses()
    {
        return [
            ['200f:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 12 ],
            ['2001:db8::1f:ffff:ffff:ffff:ffff',        59 ],
            ['2001:db8::bff:ffff:ffff:ffff',            70 ],
            ['2001:db8::a60:8a2e:1fff:ffff',            99 ],
            ['2001:db8::a60:8a2e:370:7334',             128],
        ];
    }

    public static function getValidInRangeIpAddresses()
    {
        return [
            ['d6be:0583:71a4:aa6d:c77d:77dd:0cec:f897', 'd6be:0583:71a4:aa6d:9d68:68f3:dc4a:ce01', 64 ],
            ['9800:ea88:00a5:cbcc:9d68:68f3:dc4a:ce01', '9800:ea88:00a5:cbcc:9d68:68f3:dc4a:ce01', 128],
            ['2d7f:424d:c574:632e:8d9d:847d:9f30:b62a', '2d7f:424d:c574:632e:8d98:0d78:ba5a:bf44', 77 ],
            ['10d4:ebf6:3401:e851:b3fd:0d78:ba5a:bf44', '7bf9:a81f:7047:b07a:f891:a849:25c7:52c8', 0  ],
            ['7bf9:a81f:7047:b07a:f891:a849:25c7:c8',   '7bf9:a81f:7047:b07a:f891:a849::',         96 ],
            ['c3f8:09b0:50c8:b06c:043c:ff4f:7f4a:e66d', 'c3f8:24d:c574:632e:8d9d:847d:9f30:b62a',  20 ],
        ];
    }

    public static function getMappedIpAddresses()
    {
        return self::getCategoryOfIpAddresses(self::MAPPED);
    }

    public static function getDerivedIpAddresses()
    {
        return self::getCategoryOfIpAddresses(self::DERIVED);
    }

    public static function getCompatibleIpAddresses()
    {
        return self::getCategoryOfIpAddresses(self::COMPATIBLE);
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

    /** {@inheritDoc} */
    public static function getCategorizedIpAddresses()
    {
        return [
            '::' => self::UNSPECIFIED | self::UNICAST_OTHER | self::COMPATIBLE,
            '::0' => self::UNSPECIFIED | self::UNICAST_OTHER | self::COMPATIBLE,
            '::1' => self::LOOPBACK | self::UNICAST_OTHER | self::COMPATIBLE,
            '::0.0.0.2' => self::PUBLIC_USE | self::UNICAST_GLOBAL | self::COMPATIBLE,
            '1::' => self::PUBLIC_USE | self::UNICAST_GLOBAL,
            'fc00::' => self::UNIQUE_LOCAL | self::UNICAST_OTHER,
            'fdff:ffff::' => self::PRIVATE_USE | self::UNIQUE_LOCAL | self::UNICAST_OTHER,
            'fe80:ffff::' => self::LINK_LOCAL,
            'fe80::' => self::LINK_LOCAL,
            'febf:ffff::' => self::LINK_LOCAL,
            'febf::' => self::LINK_LOCAL,
            'febf:ffff:ffff:ffff:ffff:ffff:ffff:ffff' => self::LINK_LOCAL,
            'fe80::ffff:ffff:ffff:ffff' => self::LINK_LOCAL,
            'fe80:0:0:1::' => self::LINK_LOCAL,
            'fec0::' => self::PUBLIC_USE | self::UNICAST_GLOBAL,
            'ff01::' => self::MULTICAST_INTERFACE_LOCAL,
            'ff02::' => self::MULTICAST_LINK_LOCAL,
            'ff03::' => self::MULTICAST_REALM_LOCAL,
            'ff04::' => self::MULTICAST_ADMIN_LOCAL,
            'ff05::' => self::MULTICAST_SITE_LOCAL,
            'ff08::' => self::MULTICAST_ORGANIZATION_LOCAL,
            'ff0e::' => self::PUBLIC_USE | self::MULTICAST_GLOBAL,
            '2001:db8:85a3::8a2e:370:7334' => self::DOCUMENTATION | self::UNICAST_OTHER,
            '2001:2::ac32:23ff:21' => self::PUBLIC_USE | self::BENCHMARKING | self::UNICAST_GLOBAL,
            '102:304:506:708:90a:b0c:d0e:f10' => self::PUBLIC_USE | self::UNICAST_GLOBAL,
            'fd00::' => self::PRIVATE_USE | self::UNIQUE_LOCAL | self::UNICAST_OTHER,
            'fdff:ffff:ffff:ffff:ffff:ffff:ffff:ffff' => self::PRIVATE_USE | self::UNIQUE_LOCAL | self::UNICAST_OTHER,
            'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff' => self::MULTICAST_OTHER,
            '::ffff:1:0' => self::PUBLIC_USE | self::UNICAST_GLOBAL | self::MAPPED,
            '::ffff:7f00:1' => self::PUBLIC_USE | self::UNICAST_GLOBAL | self::MAPPED | self::LOOPBACK_MAPPED,
            '::ffff:1234:5678' => self::PUBLIC_USE | self::UNICAST_GLOBAL | self::MAPPED,
            '0000:0000:0000:0000:0000:ffff:7f00:a001' => self::PUBLIC_USE | self::UNICAST_GLOBAL | self::MAPPED | self::LOOPBACK_MAPPED,
            '2002::' => self::PUBLIC_USE | self::UNICAST_GLOBAL | self::DERIVED,
            '2002:7f00:1::' => self::PUBLIC_USE | self::UNICAST_GLOBAL | self::DERIVED | self::LOOPBACK_DERIVED,
            '2002:1234:4321:0:00:000:0000::' => self::PUBLIC_USE | self::UNICAST_GLOBAL | self::DERIVED,
            '::7f00:1' => self::PUBLIC_USE | self::UNICAST_GLOBAL | self::COMPATIBLE | self::LOOPBACK_COMPATIBLE,
            '::12.34.56.78' => self::PUBLIC_USE | self::UNICAST_GLOBAL | self::COMPATIBLE,
            '0::000:0000:b12:cab' => self::PUBLIC_USE | self::UNICAST_GLOBAL | self::COMPATIBLE,
            '1cc9:7d7f:2a9f:cabd:9186:2be5:bef1:6a54' => self::PUBLIC_USE | self::UNICAST_GLOBAL,
            'b638:cc70:716:c4d4:f69c:4ee3:6c65:a0b2' => self::PUBLIC_USE | self::UNICAST_GLOBAL,
            '140c:12f1:6e6f:c0bb:980e:3816:3e52:1193' => self::PUBLIC_USE | self::UNICAST_GLOBAL,
            '7a30:bf4:4c6c:8dc1:e340:774d:6487:3822' => self::PUBLIC_USE | self::UNICAST_GLOBAL,
            '6af8:1ceb:eaae:104a:829c:e76e:5802:13f8' => self::PUBLIC_USE | self::UNICAST_GLOBAL,
            '3e48:c9fd:c569:f5dd:ee36:8075:691b:8234' => self::PUBLIC_USE | self::UNICAST_GLOBAL,
            'cab2:4f27:790f:cf03:5241:9eff:aba5:bb5c' => self::PUBLIC_USE | self::UNICAST_GLOBAL,
            'e896:8866:872b:bd4f:6d60:7aa8:ebe5:36f1' => self::PUBLIC_USE | self::UNICAST_GLOBAL,
        ];
    }

    /** {@inheritDoc} */
    public static function getCategoryOfIpAddresses($category)
    {
        $data = [];
        $true = $false = 0;
        foreach (self::getCategorizedIpAddresses() as $ipAddress => $categories) {
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
