<?php

namespace Darsyn\IP;

class InvalidIpAddressException extends \InvalidArgumentException
{
    private $ip;

    /**
     * @param string $ip
     * @param \Exception $previous
     */
    public function __construct($ip, \Exception $previous = null)
    {
        $this->ip = $ip;
        $message = is_string($ip)
            ? sprintf('The IP address supplied, "%s", is not valid.', $ip)
            : 'The IP address supplied is not valid.';
        parent::__construct($message, null, $previous);
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }
}
