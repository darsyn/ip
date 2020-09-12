<?php declare(strict_types=1);

namespace Darsyn\IP\Exception;

class InvalidCidrException extends IpException
{
    /** @var mixed $cidr */
    private $cidr;

    public function __construct(int $cidr, int $length, ?\Exception $previous = null)
    {
        $this->cidr = $cidr;
        parent::__construct(\sprintf(
            'The CIDR supplied is not valid; it must be an integer between 0 and %d.',
            $length * 8
        ), 0, $previous);
    }

    public function getSuppliedCidr(): int
    {
        return $this->cidr;
    }
}
