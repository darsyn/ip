<?php

declare(strict_types=1);

namespace Darsyn\IP\Exception;

class OverflowException extends IpException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('The arithmetic operation overflowed the bounds of the address space.', 0, $previous);
    }
}
