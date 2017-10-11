<?php

namespace Darsyn\IP\Exception;

class InvalidCidrException extends \InvalidArgumentException
{
    private $cidr;

    public function __construct($cidr, \Exception $previous = null)
    {
        $this->cidr = $cidr;
        $message = is_int($cidr)
            ? 'The CIDR supplied is not valid; it must be between 0 and 128.'
            : 'The CIDR supplied is not valid; it must be an integer between 0 and 128.';
        parent::__construct($message, null, $previous);
    }

    public function getSuppliedCidr()
    {
        return $this->cidr;
    }
}
