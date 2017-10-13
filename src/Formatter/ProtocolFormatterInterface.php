<?php

namespace Darsyn\IP\Formatter;

interface ProtocolFormatterInterface
{
    /**
     * @param string $ip
     * @throws \Darsyn\IP\Exception\Formatter\FormatException
     * @return string
     */
    public function format($ip);
}
