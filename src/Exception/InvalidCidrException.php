<?php

namespace Darsyn\IP\Exception;

class InvalidCidrException extends IpException
{
    /** @var mixed $cidr */
    private $cidr;

    /**
     * Constructor
     * .
     * @param int $cidr
     * @param int $length
     * @param \Exception|null $previous
     */
    public function __construct($cidr, $length, \Exception $previous = null)
    {
        $this->cidr = $cidr;
        $message = \is_int($length)
            ? \sprintf('The CIDR supplied is not valid; it must be an integer between 0 and %d.', $length * 8)
            : 'The CIDR supplied is not valid; it must be an integer.';
        parent::__construct($message, null, $previous);
    }

    /**
     * @return mixed
     */
    public function getSuppliedCidr()
    {
        return $this->cidr;
    }
}
