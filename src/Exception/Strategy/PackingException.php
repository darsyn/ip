<?php declare(strict_types=1);

namespace Darsyn\IP\Exception\Strategy;

use Darsyn\IP\Exception\IpException;
use Darsyn\IP\Strategy\EmbeddingStrategyInterface;

class PackingException extends IpException
{
    /** @var string $binary */
    private $binary;

    /** @var \Darsyn\IP\Strategy\EmbeddingStrategyInterface $embeddingStrategy */
    private $embeddingStrategy;

    public function __construct(
        string $binary,
        EmbeddingStrategyInterface $embeddingStrategy,
        ?\Throwable $previous = null
    ) {
        $this->binary = $binary;
        $this->embeddingStrategy = $embeddingStrategy;
        parent::__construct(\sprintf(
            'Could not pack IPv4 address into IPv6 binary string using the "%s" strategy.',
            \get_class($embeddingStrategy)
        ), 0, $previous);
    }

    public function getSuppliedBinary(): string
    {
        return $this->binary;
    }

    public function getEmbeddingStrategy(): EmbeddingStrategyInterface
    {
        return $this->embeddingStrategy;
    }
}
