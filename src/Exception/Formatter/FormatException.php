<?php declare(strict_types=1);

namespace Darsyn\IP\Exception\Formatter;

use Darsyn\IP\Exception\IpException;

class FormatException extends IpException
{
    /** @var string $binary */
    private $binary;

    public function __construct(string $binary, ?\Throwable $previous = null)
    {
        $this->binary = $binary;
        parent::__construct(
            'Cannot format invalid binary sequence; must be a string either 4 or 16 bytes long.',
            0,
            $previous
        );
    }

    public function getSuppliedBinary(): string
    {
        return $this->binary;
    }
}
