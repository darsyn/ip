<?php

namespace Darsyn\IP\Exception\Strategy;

use Darsyn\IP\Exception\IpException;
use Darsyn\IP\Strategy\EmbeddingStrategyInterface;

class ExtractionException extends IpException
{
    /** @var string $binary */
    private $binary;

    /** @var \Darsyn\IP\Strategy\EmbeddingStrategyInterface $embeddingStrategy */
    private $embeddingStrategy;

    /**
     * @param string $binary
     * @param \Darsyn\IP\Strategy\EmbeddingStrategyInterface $embeddingStrategy
     * @param \Exception|null $previous
     */
    public function __construct($binary, EmbeddingStrategyInterface $embeddingStrategy, \Exception $previous = null)
    {
        $this->binary = $binary;
        $this->embeddingStrategy = $embeddingStrategy;
        parent::__construct(\sprintf(
            'Could not extract IPv4 address from IPv6 binary string using the "%s" strategy.',
            \get_class($embeddingStrategy)
        ), 0, $previous);
    }

    /**
     * @return string
     */
    public function getSuppliedBinary()
    {
        return $this->binary;
    }

    /**
     * @return \Darsyn\IP\Strategy\EmbeddingStrategyInterface
     */
    public function getEmbeddingStrategy()
    {
        return $this->embeddingStrategy;
    }
}
