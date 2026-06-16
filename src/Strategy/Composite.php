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
class Composite implements CanonicalEmbeddingInterface
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

    /** @deprecated Use packIntoCanonical() instead. */
    public function pack(string $binary): string
    {
        return $this->packIntoCanonical($binary);
    }

    public function packIntoCanonical(string $ipv4): string
    {
        if ($this->packer instanceof CanonicalEmbeddingInterface) {
            return $this->packer->packIntoCanonical($ipv4);
        }
        // Graceful degradation for a userland packer predating the bridge.
        /** @phpstan-ignore method.deprecated */
        return $this->packer->pack($ipv4);
    }

    /**
     * Delegate to the first strategy (in constructor order) that recognises the
     * supplied IPv6 address, mirroring extract().
     */
    public function packIntoNonCanonical(string $ipv6, string $ipv4): string
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->isEmbedded($ipv6)) {
                if ($strategy instanceof CanonicalEmbeddingInterface) {
                    return $strategy->packIntoNonCanonical($ipv6, $ipv4);
                }
                /** @phpstan-ignore method.deprecated */
                return $strategy->pack($ipv4);
            }
        }
        throw new StrategyException\PackingException($ipv6, $this);
    }
}
