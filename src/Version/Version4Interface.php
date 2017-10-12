<?php

namespace Darsyn\IP\Version;

use Darsyn\IP\IpInterface;

interface Version4Interface extends IpInterface
{
    public function getSomethingAddress();
}
