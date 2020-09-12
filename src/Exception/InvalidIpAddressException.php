<?php declare(strict_types=1);

namespace Darsyn\IP\Exception;

class InvalidIpAddressException extends IpException
{
    /** @var string $ip */
    private $ip;

    public function __construct(string $ip, ?\Exception $previous = null)
    {
        $this->ip = $ip;
        parent::__construct('The IP address supplied is not valid.', 0, $previous);
    }

    public function getSuppliedIp(): string
    {
        return $this->ip;
    }
}
