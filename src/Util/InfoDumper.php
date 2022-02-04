<?php

namespace Darsyn\IP\Util;

use Darsyn\IP\IpInterface;
use Darsyn\IP\Version;

class InfoDumper
{
    public static function dump(IpInterface $ip)
    {
        $info = [];
        switch (true) {
            case $ip instanceof Version\MultiVersionInterface:
                $info['Address'] = $ip->getProtocolAppropriateAddress();
                break;
            case $ip instanceof Version\Version6Interface:
                $info['Address'] = $ip->getCompactedAddress();
                break;
            case $ip instanceof Version\Version4Interface:
                $info['Address'] = $ip->getDotAddress();
                break;
        }
        $info['Version'] = $ip->getVersion();
        if ($ip instanceof Version\MultiVersionInterface || $ip->isEmbedded()) {
            switch (true) {
                case $ip->isCompatible():
                    $info['Strategy'] = 'Compatible';
                    break;
                case $ip->isDerived():
                    $info['Strategy'] = 'Derived';
                    break;
                case $ip->isMapped():
                    $info['Strategy'] = 'Mapped';
                    break;
                default:
                    $info['Strategy'] = null;
            }
        }
        $info['Extra'] = [
            'Link Local' => $ip->isLinkLocal(),
            'Loopback' => $ip->isLoopback(),
            'Multicast' => $ip->isMulticast(),
            'Private Use' => $ip->isPrivateUse(),
            'Unspecified' => $ip->isUnspecified(),
        ];
        return $info;
    }
}
