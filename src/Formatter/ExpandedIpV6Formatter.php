<?php

namespace Darsyn\IP\Formatter;

class ExpandedIpV6Formatter extends ConsistentFormatter
{
    protected function ntopVersion6($hex)
    {
        $hex = unpack("H*hex", inet_pton(parent::ntopVersion6($hex)));
        return substr(preg_replace("/([A-f0-9]{4})/", "$1:", $hex['hex']), 0, -1);
    }
}
