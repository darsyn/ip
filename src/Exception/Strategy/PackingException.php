<?php

namespace Darsyn\IP\Exception\Strategy;

use Darsyn\IP\Exception\IpException;
use Darsyn\IP\Strategy\EmbeddingStrategyInterface;

class PackingException extends IpException
{
    /** @var string $binary */
    private $binary;

    /** @var \Darsyn\IP\Strategy\EmbeddingStrategyInterface $embeddingStrategy */
    private $embeddingStrategy;

    /**
     * @param string $binary
     * @param EmbeddingStrategyInterface $embeddingStrategy
     * @param \Exception|null $previous
     */
    public function __construct($binary, EmbeddingStrategyInterface $embeddingStrategy, \Exception $previous = null)
    {
        $this->binary = $binary;
        $this->embeddingStrategy = $embeddingStrategy;
        parent::__construct(\sprintf(
            'Could not pack IPv4 address into IPv6 binary string using the "%s" strategy.',
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
