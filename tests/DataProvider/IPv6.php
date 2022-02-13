<?php

namespace Darsyn\IP\Tests\DataProvider;

class IPv6
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

    public static function getCommonCidrValues()
    {
        return [
            ['cea9:ae3d:9fa6:a752:189a:9057:a38d:845c', 'cea9:ae3d:9fa6:a752:189d:1dfe:b689:f71e',  77 ],
            ['2870:7f44:a8c3:4ca8:dafa:bb1c:72e7:9fb6', '2870:7f44:a8c3:4ca8:dafa:bb5c:2f74:80be',  89 ],
            ['7253:1a1c:7555:6ac6:ea61:bbfd:d69c:f18f', '7253:1a1c:7555:6ac6:ea61:bbfd:d69e:7b37',  110],
            ['ce59:af1c:ba23:d57c:ae06:3c80:1127:2436', 'ce59:af1c:ba23:d54f:3007:746a:b63e:9888',  58 ],
            ['3ad3:6aa2:7985:5e2b:919e:a13e:5e2b:136f', '3ad3:6aa2:7985:5e2b:919e:a1b5:4032:cab0',  88 ],
            ['d46a:2c00:6779:8aef:f833:7219:7944:e577', 'd46a:2c00:6779:8a1f:9e1:87ca:6649:530a',   56 ],
            ['21b4:913:7f19:9336:3c2b:6eff:85c0:684b',  '21b4:913:7f19:934e:8a94:19e5:910c:5fc5',   57 ],
            ['7a13:6e44:96ae:419f:b402:b9c6:c7e0:9978', '7a13:6e4a:a50a:ca54:e74f:8c59:53db:a535',  28 ],
            ['bf1:ca13:bf30:dda9:5276:4565:1a41:f6ac',  'bf1:ca18:7dc9:e941:69b2:38e3:cbb2:ddf8',   28 ],
            ['9b8d:c55d:f3e9:5593:50e5:e933:2eb0:110',  '9b8d:c55d:f3e9:5593:50a4:29d9:93d4:fccc',  73 ],
            ['9b26:3549:9cf:84ba:6583:fb45:e3a:7cf2',   '9b26:3549:9cf:84ba:6586:74d7:e24e:91db',   77 ],
            ['52dd:91e6:f55d:7a30:197e:1fa7:337d:22ff', '52dd:91e6:f55d:7a30:197e:1fa7:3a2c:ecff',  100],
            ['e51b:81af:1a16:a3c1:8f53:b4c1:d67e:6055', 'e51b:81af:1a16:a3b5:c255:23ad:34da:6069',  57 ],
            ['cb5:c011:d0e9:546c:1f08:a09e:8116:8365',  '1a15:12c1:6dd:a19:9339:488f:f31f:3edb',    3] ,
            ['9c87:6dce:cd4e:14bb:87dc:9b14:cc8a:fef3', '9c87:6dce:cd4e:14bb:87dc:9b14:da23:f3f3',  99 ],
            ['8cda:85cb:3911:57d3:9bd9:6fd2:e8d2:9521', '8cda:85cb:3908:b2cd:eb43:f94d:dee3:8a4b',  43 ],
        ];
    }

    public static function getMappedIpAddresses()
    {
        return [
            ['::ffff:1:0',                              true ],
            ['::fff:1:0',                               false],
            ['1::ffff:b12:cab',                         false],
            ['::ffff:7f00:1',                           true ],
            ['::ffff:1234:5678',                        true ],
            ['0000:0000:0000:0000:0000:ffff:7f00:a001', true ],
            ['::fff:7f00:1',                            false],
            ['a::ffff:7f00:1',                          false],
            ['2001:db8::a60:8a2e:370:7334',             false],
        ];
    }

    public static function getDerivedIpAddresses()
    {
        return [
            ['2002::',                          true ],
            ['2002:7f00:1::',                   true ],
            ['2002:1234:4321:0:00:000:0000::',  true ],
            ['2001:7f00:1::',                   false],
            ['2002:7f00:1::a',                  false],
            ['2002::1',                         false],
        ];
    }

    public static function getCompatibleIpAddresses()
    {
        return  [
            ['::7f00:1',                                true ],
            ['::1',                                     true ],
            ['::12.34.56.78',                           true ],
            ['0::000:0000:b12:cab',                     true ],
            ['2002:7f00:1::',                           false],
            ['9800:ea88:a5:cbcc:9d68:68f3:dc4a:ce01',   false],
            ['::',                                      true ],
        ];
    }

    public static function getLinkLocalIpAddresses()
    {
        return [
            ['fe7f:ffff:ffff:ffff:ffff:ffff:ffff:ffff', false],
            ['fe80::',                                  true ],
            ['febf:ffff:ffff:ffff:ffff:ffff:ffff:ffff', true ],
            ['fec0::',                                  false],
        ];
    }

    public static function getLoopbackIpAddresses()
    {
        return [
            ['::1', true ],
            ['::2', false],
            ['1::', false],
        ];
    }

    public static function getMulticastIpAddresses()
    {
        return [
            ['feff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', false],
            ['ff00::',                                  true ],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', true ],
        ];
    }

    public static function getPrivateUseIpAddresses()
    {
        return [
            ['fcff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', false],
            ['fd00::',                                  true ],
            ['fdff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', true ],
            ['fe00::',                                  false],
        ];
    }

    public static function getUnspecifiedIpAddresses()
    {
        return [
            ['::0',             true ],
            ['::',              true ],
            ['::1',             false],
            ['2002:7f00:1::',   false],
        ];
    }
}
