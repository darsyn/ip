<?php

declare(strict_types=1);

namespace Darsyn\IP\Exception;

class InvalidIpAddressException extends IpException
{
    /** @var mixed $ip */
    private $ip;

    /**
     * Constructor
     *
     * @param scalar $ip
     */
    public function __construct($ip, ?\Exception $previous = null)
    {
        $this->ip = $ip;
        parent::__construct('The IP address supplied is not valid.', 0, $previous);
    }

    /**
     * @return mixed
     */
    public function getSuppliedIp()
    {
        return $this->ip;
    }
}
