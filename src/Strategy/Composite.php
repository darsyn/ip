<?php

declare(strict_types=1);

namespace Darsyn\IP\Strategy;

use Darsyn\IP\Exception\Strategy as StrategyException;

/**
 * Aggregates several embedding strategies behind a single strategy. An address
 * is recognised as embedded if any of the underlying strategies recognises it,
 * and extraction is delegated to the first strategy (in constructor order) that
 * recognises the address.
 *
 * Packing is asymmetric: only the first strategy supplied (the "packer") is
 * ever used to embed an IPv4 address into IPv6, so a Composite can recognise
 * several embedding schemes on input while always producing a single canonical
 * form on output.
 */
class Composite implements EmbeddingStrategyInterface
{
    /** @var EmbeddingStrategyInterface $packer */
    private $packer;

    /** @var list<EmbeddingStrategyInterface> $strategies */
    private $strategies;

    public function __construct(EmbeddingStrategyInterface $packer, EmbeddingStrategyInterface ...$additional)
    {
        $this->packer = $packer;
        $this->strategies = [$packer];
        foreach ($additional as $strategy) {
            $this->strategies[] = $strategy;
        }
    }

    public function isEmbedded(string $binary): bool
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->isEmbedded($binary)) {
                return true;
            }
        }
        return false;
    }

    public function extract(string $binary): string
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->isEmbedded($binary)) {
                return $strategy->extract($binary);
            }
        }
        throw new StrategyException\ExtractionException($binary, $this);
    }

    public function pack(string $binary): string
    {
        return $this->packer->pack($binary);
    }
}
