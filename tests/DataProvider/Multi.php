<?php

namespace Darsyn\IP\Tests\DataProvider;

use Darsyn\IP\Strategy\Compatible;
use Darsyn\IP\Strategy\Derived;
use Darsyn\IP\Strategy\Mapped;

class Multi
{
    public static function getValidBinarySequences()
    {
        return [
            // [ constructor value, expected hex, expected expanded address, expected compacted address, dot notation ].
            [pack('H*', '00000000000000000000000000000000'), '00000000000000000000000000000000', '0000:0000:0000:0000:0000:0000:0000:0000', '::',                                      null              ],
            [pack('H*', 'd6be058371a4aa6dc77d77dd0cecf897'), 'd6be058371a4aa6dc77d77dd0cecf897', 'd6be:0583:71a4:aa6d:c77d:77dd:0cec:f897', 'd6be:583:71a4:aa6d:c77d:77dd:cec:f897',   null              ],
            [pack('H*', '2d7f424dc574632e8d9d847d9f30b62a'), '2d7f424dc574632e8d9d847d9f30b62a', '2d7f:424d:c574:632e:8d9d:847d:9f30:b62a', '2d7f:424d:c574:632e:8d9d:847d:9f30:b62a', null              ],
            [pack('H*', '10d4ebf63401e851b3fd0d78ba5abf44'), '10d4ebf63401e851b3fd0d78ba5abf44', '10d4:ebf6:3401:e851:b3fd:0d78:ba5a:bf44', '10d4:ebf6:3401:e851:b3fd:d78:ba5a:bf44',  null              ],
            [pack('H*', '7bf9a81f7047b07af891a84925c752c8'), '7bf9a81f7047b07af891a84925c752c8', '7bf9:a81f:7047:b07a:f891:a849:25c7:52c8', '7bf9:a81f:7047:b07a:f891:a849:25c7:52c8', null              ],
            [pack('H*', '9800ea8800a5cbcc9d6868f3dc4ace01'), '9800ea8800a5cbcc9d6868f3dc4ace01', '9800:ea88:00a5:cbcc:9d68:68f3:dc4a:ce01', '9800:ea88:a5:cbcc:9d68:68f3:dc4a:ce01',   null              ],
            [pack('H*', 'c3f889b050c8b06c043cff4f7f4ae66d'), 'c3f889b050c8b06c043cff4f7f4ae66d', 'c3f8:89b0:50c8:b06c:043c:ff4f:7f4a:e66d', 'c3f8:89b0:50c8:b06c:43c:ff4f:7f4a:e66d',  null              ],
            [pack('H*', 'ffffffffffffffffffffffffffffffff'), 'ffffffffffffffffffffffffffffffff', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', null              ],
            ['1234567890123456',                             '31323334353637383930313233343536', '3132:3334:3536:3738:3930:3132:3334:3536', '3132:3334:3536:3738:3930:3132:3334:3536', null              ],
            [pack('H*', '00000000000000000000ffff00000000'), '00000000000000000000ffff00000000', '0000:0000:0000:0000:0000:ffff:0000:0000', '::ffff:0:0',                              '0.0.0.0'         ],
            [pack('H*', '00000000000000000000ffff71637a89'), '00000000000000000000ffff71637a89', '0000:0000:0000:0000:0000:ffff:7163:7a89', '::ffff:7163:7a89',                        '113.99.122.137'  ],
            [pack('H*', '00000000000000000000ffff4708d36c'), '00000000000000000000ffff4708d36c', '0000:0000:0000:0000:0000:ffff:4708:d36c', '::ffff:4708:d36c',                        '71.8.211.108'    ],
            [pack('H*', '00000000000000000000ffffc8fa3d9b'), '00000000000000000000ffffc8fa3d9b', '0000:0000:0000:0000:0000:ffff:c8fa:3d9b', '::ffff:c8fa:3d9b',                        '200.250.61.155'  ],
            [pack('H*', '00000000000000000000ffffdb37478d'), '00000000000000000000ffffdb37478d', '0000:0000:0000:0000:0000:ffff:db37:478d', '::ffff:db37:478d',                        '219.55.71.141'   ],
            [pack('H*', '00000000000000000000ffffae823cc4'), '00000000000000000000ffffae823cc4', '0000:0000:0000:0000:0000:ffff:ae82:3cc4', '::ffff:ae82:3cc4',                        '174.130.60.196'  ],
            [pack('H*', '00000000000000000000ffff0c0679fc'), '00000000000000000000ffff0c0679fc', '0000:0000:0000:0000:0000:ffff:0c06:79fc', '::ffff:c06:79fc',                         '12.6.121.252'    ],
            [pack('H*', '00000000000000000000ffffffffffff'), '00000000000000000000ffffffffffff', '0000:0000:0000:0000:0000:ffff:ffff:ffff', '::ffff:ffff:ffff',                        '255.255.255.255' ],
        ];
    }

    public static function getValidProtocolIpAddresses()
    {
        return [
            // [ constructor value, expected hex, expected expanded address, expected compacted address, dot notation ].
            ['::1',                             '00000000000000000000000000000001', '0000:0000:0000:0000:0000:0000:0000:0001', '::1',                         null              ],
            ['::b12:cab',                       '0000000000000000000000000b120cab', '0000:0000:0000:0000:0000:0000:0b12:0cab', '::b12:cab',                   null              ],
            ['::12.34.56.78',                   '0000000000000000000000000c22384e', '0000:0000:0000:0000:0000:0000:0c22:384e', '::c22:384e',                  null              ],
            ['::ffff:0c22:384e',                '00000000000000000000ffff0c22384e', '0000:0000:0000:0000:0000:ffff:0c22:384e', '::ffff:c22:384e',             '12.34.56.78'     ],
            ['2002:c22:384e::',                 '20020c22384e00000000000000000000', '2002:0c22:384e:0000:0000:0000:0000:0000', '2002:c22:384e::',             null              ],
            ['2001:db8::a60:8a2e:370:7334',     '20010db8000000000a608a2e03707334', '2001:0db8:0000:0000:0a60:8a2e:0370:7334', '2001:db8::a60:8a2e:370:7334', null              ],
            ['2001:db8::a60:8a2e:0:7334',       '20010db8000000000a608a2e00007334', '2001:0db8:0000:0000:0a60:8a2e:0000:7334', '2001:db8::a60:8a2e:0:7334',   null              ],
            ['2001:0db8:0::a60:8a2e:0370:7334', '20010db8000000000a608a2e03707334', '2001:0db8:0000:0000:0a60:8a2e:0370:7334', '2001:db8::a60:8a2e:370:7334', null              ],
            ['119.14.113.44',                   '00000000000000000000ffff770e712c', '0000:0000:0000:0000:0000:ffff:770e:712c', '::ffff:770e:712c',            '119.14.113.44'   ],
            ['83.197.36.73',                    '00000000000000000000ffff53c52449', '0000:0000:0000:0000:0000:ffff:53c5:2449', '::ffff:53c5:2449',            '83.197.36.73'    ],
            ['18.118.59.40',                    '00000000000000000000ffff12763b28', '0000:0000:0000:0000:0000:ffff:1276:3b28', '::ffff:1276:3b28',            '18.118.59.40'    ],
            ['100.39.68.128',                   '00000000000000000000ffff64274480', '0000:0000:0000:0000:0000:ffff:6427:4480', '::ffff:6427:4480',            '100.39.68.128'   ],
            ['68.192.97.34',                    '00000000000000000000ffff44c06122', '0000:0000:0000:0000:0000:ffff:44c0:6122', '::ffff:44c0:6122',            '68.192.97.34'    ],
            ['141.216.7.75',                    '00000000000000000000ffff8dd8074b', '0000:0000:0000:0000:0000:ffff:8dd8:074b', '::ffff:8dd8:74b',             '141.216.7.75'    ],
            ['151.197.48.205',                  '00000000000000000000ffff97c530cd', '0000:0000:0000:0000:0000:ffff:97c5:30cd', '::ffff:97c5:30cd',            '151.197.48.205'  ],
            ['182.234.197.141',                 '00000000000000000000ffffb6eac58d', '0000:0000:0000:0000:0000:ffff:b6ea:c58d', '::ffff:b6ea:c58d',            '182.234.197.141' ],
        ];
    }

    public static function getValidIpAddresses()
    {
        return array_merge(self::getValidBinarySequences(), self::getValidProtocolIpAddresses());
    }

    public static function getValidIpVersion4Addresses()
    {
        return array_filter(self::getValidIpAddresses(), function (array $row) {
            return is_string($row[4]);
        });
    }

    public static function getValidIpVersion6Addresses()
    {
        return array_filter(self::getValidIpAddresses(), function (array $row) {
            return !is_string($row[4]);
        });
    }

    public static function getInvalidIpAddresses()
    {
        return [
            ['2001:db8::a60:8a2e:370g:7334'],
            ['1.2.3'],
            ['12.34.56.256'],
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

    public static function getIpAddressVersions()
    {
        return array_merge(
            array_map(function ($row) {
                return [$row[0], 4];
            }, self::getValidIpVersion4Addresses()),
            array_map(function ($row) {
                return [$row[0], 6];
            }, self::getValidIpVersion6Addresses())
        );
    }

    public static function getValidCidrValues()
    {
        return IPv6::getValidCidrValues();
    }

    public static function getInvalidCidrValues()
    {
        return IPv6::getInvalidCidrValues();
    }

    public static function getNetworkIpAddresses()
    {
        return array_merge(
            array_map(function ($row) {
                array_unshift($row, '12.34.56.78');
                return $row;
            }, IPv4::getNetworkIpAddresses()),
            array_map(function ($row) {
                array_unshift($row, '2001:db8::a60:8a2e:370:7334');
                return $row;
            }, IPv6::getNetworkIpAddresses())

        );
    }

    public static function getBroadcastIpAddresses()
    {
        return array_merge(
            array_map(function ($row) {
                array_unshift($row, '12.34.56.78');
                return $row;
            }, IPv4::getBroadcastIpAddresses()),
            array_map(function ($row) {
                array_unshift($row, '2001:db8::a60:8a2e:370:7334');
                return $row;
            }, IPv6::getBroadcastIpAddresses())
        );
    }

    public static function getValidInRangeIpAddresses()
    {
        return array_merge(
            array_map(function ($row) {
                array_push($row, true);
                return $row;
            }, IPv4::getValidInRangeIpAddresses()),
            array_map(function ($row) {
                array_push($row, true);
                return $row;
            }, IPv6::getValidInRangeIpAddresses()),
            [
                // Mix IPv6 and IPv4 addresses together.
                ['::ffff:12.34.56.78', '12.34.56.78', 30],
            ]
        );
    }

    public static function getCommonCidrValues()
    {
        return array_merge(
            IPv4::getCommonCidrValues(),
            IPv6::getCommonCidrValues()
        );
    }

    public static function getEmbeddedAddresses()
    {
        return array_merge(
            array_map(function ($row) {
                return [$row[0], true];
            }, self::getValidIpVersion4Addresses()),
            array_map(function ($row) {
                return [$row[0], false];
            }, self::getValidIpVersion6Addresses())
        );
    }

    public function getMappedIpAddresses()
    {
        return IPv6::getMappedIpAddresses();
    }

    public function getDerivedIpAddresses()
    {
        return IPv6::getDerivedIpAddresses();
    }

    public function getCompatibleIpAddresses()
    {
        return  IPv6::getCompatibleIpAddresses();
    }

    public static function getLinkLocalIpAddresses()
    {
        return array_merge(IPv4::getLinkLocalIpAddresses(), IPv6::getLinkLocalIpAddresses());
    }

    public static function getLoopbackIpAddresses()
    {
        return array_merge(IPv4::getLoopbackIpAddresses(), IPv6::getLoopbackIpAddresses());
    }

    public static function getMulticastIpAddresses()
    {
        return array_merge(IPv4::getMulticastIpAddresses(), IPv6::getMulticastIpAddresses());
    }

    public static function getPrivateUseIpAddresses()
    {
        return array_merge(IPv4::getPrivateUseIpAddresses(), IPv6::getPrivateUseIpAddresses());
    }

    public static function getUnspecifiedIpAddresses()
    {
        return array_merge(IPv4::getUnspecifiedIpAddresses(), IPv6::getUnspecifiedIpAddresses());
    }

    public static function getEmbeddingStrategyIpAddresses()
    {
        return [
            [Compatible::class, '0000:0000:0000:0000:0000:0000:0c22:384e', '12.34.56.78'     ],
            [Compatible::class, '0000:0000:0000:0000:0000:0000:770e:712c', '119.14.113.44'   ],
            [Compatible::class, '0000:0000:0000:0000:0000:0000:53c5:2449', '83.197.36.73'    ],
            [Compatible::class, '0000:0000:0000:0000:0000:0000:1276:3b28', '18.118.59.40'    ],
            [Compatible::class, '0000:0000:0000:0000:0000:0000:6427:4480', '100.39.68.128'   ],
            [Compatible::class, '0000:0000:0000:0000:0000:0000:44c0:6122', '68.192.97.34'    ],
            [Compatible::class, '0000:0000:0000:0000:0000:0000:8dd8:074b', '141.216.7.75'    ],
            [Compatible::class, '0000:0000:0000:0000:0000:0000:97c5:30cd', '151.197.48.205'  ],
            [Compatible::class, '0000:0000:0000:0000:0000:0000:b6ea:c58d', '182.234.197.141' ],
            [Compatible::class, '0000:0000:0000:0000:0000:0000:0000:0000', '0.0.0.0'         ],
            [Compatible::class, '0000:0000:0000:0000:0000:0000:7163:7a89', '113.99.122.137'  ],
            [Compatible::class, '0000:0000:0000:0000:0000:0000:4708:d36c', '71.8.211.108'    ],
            [Compatible::class, '0000:0000:0000:0000:0000:0000:c8fa:3d9b', '200.250.61.155'  ],
            [Compatible::class, '0000:0000:0000:0000:0000:0000:db37:478d', '219.55.71.141'   ],
            [Compatible::class, '0000:0000:0000:0000:0000:0000:ae82:3cc4', '174.130.60.196'  ],
            [Compatible::class, '0000:0000:0000:0000:0000:0000:0c06:79fc', '12.6.121.252'    ],
            [Compatible::class, '0000:0000:0000:0000:0000:0000:ffff:ffff', '255.255.255.255' ],

            [Derived::class,    '2002:0c22:384e:0000:0000:0000:0000:0000', '12.34.56.78'     ],
            [Derived::class,    '2002:770e:712c:0000:0000:0000:0000:0000', '119.14.113.44'   ],
            [Derived::class,    '2002:53c5:2449:0000:0000:0000:0000:0000', '83.197.36.73'    ],
            [Derived::class,    '2002:1276:3b28:0000:0000:0000:0000:0000', '18.118.59.40'    ],
            [Derived::class,    '2002:6427:4480:0000:0000:0000:0000:0000', '100.39.68.128'   ],
            [Derived::class,    '2002:44c0:6122:0000:0000:0000:0000:0000', '68.192.97.34'    ],
            [Derived::class,    '2002:8dd8:074b:0000:0000:0000:0000:0000', '141.216.7.75'    ],
            [Derived::class,    '2002:97c5:30cd:0000:0000:0000:0000:0000', '151.197.48.205'  ],
            [Derived::class,    '2002:b6ea:c58d:0000:0000:0000:0000:0000', '182.234.197.141' ],
            [Derived::class,    '2002:0000:0000:0000:0000:0000:0000:0000', '0.0.0.0'         ],
            [Derived::class,    '2002:7163:7a89:0000:0000:0000:0000:0000', '113.99.122.137'  ],
            [Derived::class,    '2002:4708:d36c:0000:0000:0000:0000:0000', '71.8.211.108'    ],
            [Derived::class,    '2002:c8fa:3d9b:0000:0000:0000:0000:0000', '200.250.61.155'  ],
            [Derived::class,    '2002:db37:478d:0000:0000:0000:0000:0000', '219.55.71.141'   ],
            [Derived::class,    '2002:ae82:3cc4:0000:0000:0000:0000:0000', '174.130.60.196'  ],
            [Derived::class,    '2002:0c06:79fc:0000:0000:0000:0000:0000', '12.6.121.252'    ],
            [Derived::class,    '2002:ffff:ffff:0000:0000:0000:0000:0000', '255.255.255.255' ],

            [Mapped::class,     '0000:0000:0000:0000:0000:ffff:0c22:384e', '12.34.56.78'     ],
            [Mapped::class,     '0000:0000:0000:0000:0000:ffff:770e:712c', '119.14.113.44'   ],
            [Mapped::class,     '0000:0000:0000:0000:0000:ffff:53c5:2449', '83.197.36.73'    ],
            [Mapped::class,     '0000:0000:0000:0000:0000:ffff:1276:3b28', '18.118.59.40'    ],
            [Mapped::class,     '0000:0000:0000:0000:0000:ffff:6427:4480', '100.39.68.128'   ],
            [Mapped::class,     '0000:0000:0000:0000:0000:ffff:44c0:6122', '68.192.97.34'    ],
            [Mapped::class,     '0000:0000:0000:0000:0000:ffff:8dd8:074b', '141.216.7.75'    ],
            [Mapped::class,     '0000:0000:0000:0000:0000:ffff:97c5:30cd', '151.197.48.205'  ],
            [Mapped::class,     '0000:0000:0000:0000:0000:ffff:b6ea:c58d', '182.234.197.141' ],
            [Mapped::class,     '0000:0000:0000:0000:0000:ffff:0000:0000', '0.0.0.0'         ],
            [Mapped::class,     '0000:0000:0000:0000:0000:ffff:7163:7a89', '113.99.122.137'  ],
            [Mapped::class,     '0000:0000:0000:0000:0000:ffff:4708:d36c', '71.8.211.108'    ],
            [Mapped::class,     '0000:0000:0000:0000:0000:ffff:c8fa:3d9b', '200.250.61.155'  ],
            [Mapped::class,     '0000:0000:0000:0000:0000:ffff:db37:478d', '219.55.71.141'   ],
            [Mapped::class,     '0000:0000:0000:0000:0000:ffff:ae82:3cc4', '174.130.60.196'  ],
            [Mapped::class,     '0000:0000:0000:0000:0000:ffff:0c06:79fc', '12.6.121.252'    ],
            [Mapped::class,     '0000:0000:0000:0000:0000:ffff:ffff:ffff', '255.255.255.255' ],


        ];
    }
}
