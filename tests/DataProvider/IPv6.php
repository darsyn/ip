<?php

namespace Darsyn\IP\Tests\DataProvider;

class IPv6
{
    public static function getValidBinarySequences()
    {
        return [
            // [ constructor value, expected hex, expected expanded address, expected compacted address ].
            [pack('H*', '00000000000000000000000000000000'), '00000000000000000000000000000000', '0000:0000:0000:0000:0000:0000:0000:0000', '::'                                     ],
            [pack('H*', 'd6be058371a4aa6dc77d77dd0cecf897'), 'd6be058371a4aa6dc77d77dd0cecf897', 'd6be:0583:71a4:aa6d:c77d:77dd:0cec:f897', 'd6be:583:71a4:aa6d:c77d:77dd:cec:f897'  ],
            [pack('H*', '2d7f424dc574632e8d9d847d9f30b62a'), '2d7f424dc574632e8d9d847d9f30b62a', '2d7f:424d:c574:632e:8d9d:847d:9f30:b62a', '2d7f:424d:c574:632e:8d9d:847d:9f30:b62a'],
            [pack('H*', '10d4ebf63401e851b3fd0d78ba5abf44'), '10d4ebf63401e851b3fd0d78ba5abf44', '10d4:ebf6:3401:e851:b3fd:0d78:ba5a:bf44', '10d4:ebf6:3401:e851:b3fd:d78:ba5a:bf44' ],
            [pack('H*', '7bf9a81f7047b07af891a84925c752c8'), '7bf9a81f7047b07af891a84925c752c8', '7bf9:a81f:7047:b07a:f891:a849:25c7:52c8', '7bf9:a81f:7047:b07a:f891:a849:25c7:52c8'],
            [pack('H*', '9800ea8800a5cbcc9d6868f3dc4ace01'), '9800ea8800a5cbcc9d6868f3dc4ace01', '9800:ea88:00a5:cbcc:9d68:68f3:dc4a:ce01', '9800:ea88:a5:cbcc:9d68:68f3:dc4a:ce01'  ],
            [pack('H*', 'c3f889b050c8b06c043cff4f7f4ae66d'), 'c3f889b050c8b06c043cff4f7f4ae66d', 'c3f8:89b0:50c8:b06c:043c:ff4f:7f4a:e66d', 'c3f8:89b0:50c8:b06c:43c:ff4f:7f4a:e66d' ],
            [pack('H*', 'ffffffffffffffffffffffffffffffff'), 'ffffffffffffffffffffffffffffffff', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'],
            ['1234567890123456',                             '31323334353637383930313233343536', '3132:3334:3536:3738:3930:3132:3334:3536', '3132:3334:3536:3738:3930:3132:3334:3536'],
        ];
    }

    public static function getValidProtocolIpAddresses()
    {
        return [
            ['::1',                             '00000000000000000000000000000001', '0000:0000:0000:0000:0000:0000:0000:0001', '::1'                        ],
            ['::b12:cab',                       '0000000000000000000000000b120cab', '0000:0000:0000:0000:0000:0000:0b12:0cab', '::b12:cab'                  ],
            ['::12.34.56.78',                   '0000000000000000000000000c22384e', '0000:0000:0000:0000:0000:0000:0c22:384e', '::c22:384e'                 ],
            ['::ffff:0c22:384e',                '00000000000000000000ffff0c22384e', '0000:0000:0000:0000:0000:ffff:0c22:384e', '::ffff:c22:384e'            ],
            ['2002:c22:384e::',                 '20020c22384e00000000000000000000', '2002:0c22:384e:0000:0000:0000:0000:0000', '2002:c22:384e::'            ],
            ['2001:db8::a60:8a2e:370:7334',     '20010db8000000000a608a2e03707334', '2001:0db8:0000:0000:0a60:8a2e:0370:7334', '2001:db8::a60:8a2e:370:7334'],
            ['2001:db8::a60:8a2e:0:7334',       '20010db8000000000a608a2e00007334', '2001:0db8:0000:0000:0a60:8a2e:0000:7334', '2001:db8::a60:8a2e:0:7334'  ],
            ['2001:0db8:0::a60:8a2e:0370:7334', '20010db8000000000a608a2e03707334', '2001:0db8:0000:0000:0a60:8a2e:0370:7334', '2001:db8::a60:8a2e:370:7334'],
        ];
    }

    public static function getValidIpAddresses()
    {
        return array_merge(self::getValidBinarySequences(), self::getValidProtocolIpAddresses());
    }

    public static function getInvalidIpAddresses()
    {
        return [
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

    public function getMappedIpAddresses()
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

    public function getDerivedIpAddresses()
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

    public function getCompatibleIpAddresses()
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
}
