<?php declare(strict_types=1);

namespace Darsyn\IP\Exception;

class WrongVersionException extends InvalidIpAddressException
{
    /** @var integer $expected */
    private $expected;

    /** @var integer $actual */
    private $actual;

    public function __construct(int $expected, int $actual, string $ip, ?\Throwable $previous = null)
    {
        $this->expected = $expected;
        $this->actual = $actual;
        parent::__construct($ip, $previous);
    }

    public function getExpectedVersion(): int
    {
        return $this->expected;
    }

    public function getActualVersion(): int
    {
        return $this->actual;
    }
}
