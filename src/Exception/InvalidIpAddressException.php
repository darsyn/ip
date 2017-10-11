<?php

namespace Darsyn\IP\Exception;

class InvalidIpAddressException extends \InvalidArgumentException
{
    private $ip;

    public function __construct($ip, \Exception $previous = null)
    {
        $this->ip = $ip;
        $message = is_string($ip)
            ? sprintf('The IP address supplied, "%s", is not valid.', $ip)
            : 'The IP address supplied is not valid.';
        parent::__construct($message, null, $previous);
    }

    public function getSuppliedIp()
    {
        return $this->ip;
    }
}
