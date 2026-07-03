<?php

declare(strict_types=1);

namespace Darsyn\IP\Version;

use Darsyn\IP\Contracts\Classification4Interface;
use Darsyn\IP\Contracts\Factory4Interface;
use Darsyn\IP\Contracts\Output4Interface;
use Darsyn\IP\IpInterface;

interface Version4Interface extends IpInterface, Classification4Interface, Output4Interface, Factory4Interface
{
    /** @deprecated Use toDotAddress() instead. */
    public function getDotAddress(/* ?ProtocolFormatterInterface $formatter = null */): string;
}
