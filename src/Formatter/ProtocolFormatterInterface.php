<?php

namespace Darsyn\IP\Formatter;

interface ProtocolFormatterInterface
{
    /**
     * Protocol to Binary
     *
     * @param string $binary
     * @throws \Darsyn\IP\Exception\Formatter\FormatException
     * @return string
     */
    public function pton($binary);

    /**
     * Binary to Protocol
     *
     * Convert
     * @param string $binary
     * @throws \Darsyn\IP\Exception\Formatter\FormatException
     * @return string
     */
    public function ntop($binary);
}
