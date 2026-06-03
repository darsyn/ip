<?php

declare(strict_types=1);

namespace Darsyn\IP\Strategy;

use Darsyn\IP\Exception\Strategy as StrategyException;

class Composite implements EmbeddingStrategyInterface
{
    /** @var list<EmbeddingStrategyInterface> */
    private $strategies;

    /** @var EmbeddingStrategyInterface */
    private $packer;

    public function __construct(
        EmbeddingStrategyInterface $packer,
        EmbeddingStrategyInterface ...$additional
    ) {
        $this->packer = $packer;
        $this->strategies = array_values(array_merge([$packer], $additional));
    }

    /**
     * Named helper constructor for matching all unambiguous, non-deprecated
     * embedding strategies.
     */
    public static function all(): self
    {
        // Explicitly NOT including the ambiguous, deprecated "Compatible"
        // embedding strategy, and using Mapped as the canonical strategy for
        // packing.
        return new self(new Mapped(), new Derived(), new Nat64(), new Teredo());
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
