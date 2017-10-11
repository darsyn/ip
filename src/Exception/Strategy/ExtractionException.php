<?php

namespace Darsyn\IP\Exception\Strategy;

use Darsyn\IP\Strategy\EmbeddingStrategyInterface;

class ExtractionException extends \LogicException
{
    /** @var string $binary */
    private $binary;

    /** @var EmbeddingStrategyInterface $embeddingStrategy */
    private $embeddingStrategy;

    public function __construct($binary, EmbeddingStrategyInterface $embeddingStrategy, \Exception $previous = null)
    {
        $this->binary = $binary;
        $this->embeddingStrategy = $embeddingStrategy;
        parent::__construct(sprintf(
            'Could not extract IPv4 address from IPv6 binary string using the "%s" strategy.',
            get_class($embeddingStrategy)
        ));
    }

    public function getSuppliedBinary()
    {
        return $this->binary;
    }

    public function getEmbeddingStrategy()
    {
        return $this->embeddingStrategy;
    }
}
