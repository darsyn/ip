<?php

namespace Darsyn\IP\Exception\Formatter;

use Darsyn\IP\Exception\IpException;

class FormatException extends IpException
{
    /** @var string $binary */
    private $binary;

    /**
     * @param string $binary
     * @param \Exception|null $previous
     */
    public function __construct($binary, \Exception $previous = null)
    {
        $this->binary = $binary;
        parent::__construct('Cannot format invalid binary sequence; must be a string either 4 or 16 bytes long.', 0, $previous);
    }

    /**
     * @return string
     */
    public function getSuppliedBinary()
    {
        return $this->binary;
    }
}
