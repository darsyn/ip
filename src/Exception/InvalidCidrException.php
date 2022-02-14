<?php

namespace Darsyn\IP\Exception;

class InvalidCidrException extends IpException
{
    /** @var mixed $cidr */
    private $cidr;

    /**
     * Constructor
     *
     * @param mixed $cidr
     * @param mixed $addressLengthInBytes
     * @param \Exception|null $previous
     */
    public function __construct($cidr, $addressLengthInBytes, \Exception $previous = null)
    {
        $this->cidr = $cidr;
        $message = 'The supplied CIDR is not valid; it must be an integer ';
        if (!\is_int($addressLengthInBytes)) {
            $message .= '(could not determine valid CIDR range).';
        } else {
            $message .= \sprintf('(between 0 and %d).', $addressLengthInBytes * 8);
        }
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return mixed
     */
    public function getSuppliedCidr()
    {
        return $this->cidr;
    }
}
