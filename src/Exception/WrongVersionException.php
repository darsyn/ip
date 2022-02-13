<?php

namespace Darsyn\IP\Exception;

class WrongVersionException extends InvalidIpAddressException
{
    /** @var int $expected */
    private $expected;

    /** @var int $actual */
    private $actual;

    /**
     * @param int $expected
     * @param int $actual
     * @param scalar $ip
     * @param \Exception|null $previous
     */
    public function __construct($expected, $actual, $ip, \Exception $previous = null)
    {
        $this->expected = $expected;
        $this->actual = $actual;
        parent::__construct($ip, $previous);
    }

    /**
     * @return int
     */
    public function getExpectedVersion()
    {
        return $this->expected;
    }

    /**
     * @return int
     */
    public function getActualVersion()
    {
        return $this->actual;
    }
}
