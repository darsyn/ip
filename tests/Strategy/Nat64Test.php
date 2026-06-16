<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\Strategy;

use Darsyn\IP\Strategy\Nat64;
use Darsyn\IP\Tests\DataProvider\Strategy\Nat64 as Nat64DataProvider;
use Darsyn\IP\Version\IPv6;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class Nat64Test extends TestCase
{
    /** @var \Darsyn\IP\Strategy\CanonicalEmbeddingInterface $strategy */
    private $strategy;

    /** @before */
    #[PHPUnit\Before]
    protected function setUpWithoutReturnDeclaration(): void
    {
        $this->strategy = Nat64::wellKnown();
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getInvalidIpAddresses')]
    public function testIsEmbeddedReturnsFalseForAStringOtherThan16BytesLong(string $value): void
    {
        $this->assertFalse($this->strategy->isEmbedded($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getValidIpAddresses')]
    public function testIsEmbedded(string $value, bool $isEmbedded): void
    {
        $this->assertSame($isEmbedded, $this->strategy->isEmbedded($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getInvalidIpAddresses')]
    public function testExceptionIsThrownWhenTryingToExtractFromStringsNot16Bytes(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\ExtractionException::class);
        $this->strategy->extract($value);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getValidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getValidSequences')]
    public function testCorrectSequenceExtractedFromIpBinary(string $ipv6, string $ipv4): void
    {
        $this->assertSame($ipv4, $this->strategy->extract($ipv6));
    }

    /**
     * @deprecated Covers the deprecated pack(); see `testPackIntoCanonical*` methods.
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getInvalidIpAddresses')]
    public function testExceptionIsThrownWhenTryingToPackStringsNot4Bytes(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\PackingException::class);
        $this->strategy->pack($value);
    }

    /**
     * @deprecated Covers the deprecated pack(); see `testPackIntoCanonical*` methods.
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getValidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getValidSequences')]
    public function testSequenceCorrectlyPackedIntoIpBinaryFromIpBinary(string $ipv6, string $ipv4): void
    {
        $this->assertSame($ipv6, $this->strategy->pack($ipv4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getNetworkSpecificSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getNetworkSpecificSequences')]
    public function testSequencesEmbedExtractAndPackWithNetworkSpecificPrefixes(string $prefixAddress, int $length, string $ipv6, string $ipv4): void
    {
        $strategy = Nat64::networkSpecific(IPv6::fromProtocol($prefixAddress), $length);
        $this->assertTrue($strategy->isEmbedded($ipv6));
        $this->assertSame($ipv4, $strategy->extract($ipv6));
        $this->assertSame($ipv6, $strategy->packIntoCanonical($ipv4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getNonMatchingNetworkSpecificSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getNonMatchingNetworkSpecificSequences')]
    public function testIsEmbeddedReturnsFalseForSequencesNotMatchingNetworkSpecificPrefix(string $prefixAddress, int $length, string $ipv6): void
    {
        $this->assertFalse(Nat64::networkSpecific(IPv6::fromProtocol($prefixAddress), $length)->isEmbedded($ipv6));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getNonCanonicalNetworkSpecificSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getNonCanonicalNetworkSpecificSequences')]
    public function testNonCanonicalSequencesRoundTripToCanonicalFormWithNetworkSpecificPrefixes(string $prefixAddress, int $length, string $nonCanonical, string $ipv4, string $canonical): void
    {
        $strategy = Nat64::networkSpecific(IPv6::fromProtocol($prefixAddress), $length);
        $this->assertTrue($strategy->isEmbedded($nonCanonical));
        $this->assertSame($ipv4, $strategy->extract($nonCanonical));
        $this->assertSame($canonical, $strategy->packIntoCanonical($strategy->extract($nonCanonical)));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getValidNetworkSpecificArguments()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getValidNetworkSpecificArguments')]
    public function testNetworkSpecificStrategyCorrectlyConstructed(string $prefixAddress, int $length, string $expectedPrefixHex): void
    {
        $strategy = Nat64::networkSpecific(IPv6::fromProtocol($prefixAddress), $length);
        $this->assertSame(\pack('H*', $expectedPrefixHex), $strategy->getPrefix());
        $this->assertSame($length, $strategy->getPrefixLength());
        $this->assertSame($prefixAddress, IPv6::fromBinary($strategy->getPrefix())->getCompactedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getInvalidNetworkSpecificArguments()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getInvalidNetworkSpecificArguments')]
    public function testExceptionIsThrownForInvalidNetworkSpecificArguments(string $prefixAddress, int $length): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Nat64::networkSpecific(IPv6::fromProtocol($prefixAddress), $length);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getZeroedNetworkSpecificArguments()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getZeroedNetworkSpecificArguments')]
    public function testBitsSetAfterPrefixLengthAreZeroedForNetworkSpecificPrefixes(string $prefixAddress, int $length, string $expectedPrefixHex): void
    {
        $strategy = Nat64::networkSpecific(IPv6::fromProtocol($prefixAddress), $length);
        $this->assertSame(\pack('H*', $expectedPrefixHex), $strategy->getPrefix());
        $this->assertSame($length, $strategy->getPrefixLength());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testWellKnownNamedConstructor(): void
    {
        $wellKnown = Nat64::wellKnown();
        $this->assertSame(\pack('H*', Nat64::WELL_KNOWN_PREFIX), $wellKnown->getPrefix());
        $this->assertSame(96, $wellKnown->getPrefixLength());
        $this->assertSame('64:ff9b::', IPv6::fromBinary($wellKnown->getPrefix())->getCompactedAddress());
        $this->assertSame(
            Nat64::networkSpecific(IPv6::fromProtocol('64:ff9b::'), 96)->packIntoCanonical(\pack('H*', 'c0000221')),
            $wellKnown->packIntoCanonical(\pack('H*', 'c0000221'))
        );
    }

    /** @test */
    #[PHPUnit\Test]
    public function testLocalUseNamedConstructor(): void
    {
        $localUse = Nat64::localUse();
        $this->assertSame(\pack('H*', Nat64::LOCAL_USE_PREFIX), $localUse->getPrefix());
        $this->assertSame(48, $localUse->getPrefixLength());
        $this->assertSame('64:ff9b:1::', IPv6::fromBinary($localUse->getPrefix())->getCompactedAddress());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getLocalUseSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getLocalUseSequences')]
    public function testSequencesEmbedExtractAndPackWithLocalUsePrefix(string $ipv6, string $ipv4): void
    {
        $strategy = Nat64::localUse();
        $this->assertTrue($strategy->isEmbedded($ipv6));
        $this->assertSame($ipv4, $strategy->extract($ipv6));
        $this->assertSame($ipv6, $strategy->packIntoCanonical($ipv4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getNonMatchingLocalUseSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getNonMatchingLocalUseSequences')]
    public function testIsEmbeddedReturnsFalseForSequencesNotMatchingLocalUsePrefix(string $ipv6): void
    {
        $this->assertFalse(Nat64::localUse()->isEmbedded($ipv6));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getNonCanonicalLocalUseSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getNonCanonicalLocalUseSequences')]
    public function testNonCanonicalSequencesRoundTripToCanonicalFormWithLocalUsePrefix(string $nonCanonical, string $ipv4, string $canonical): void
    {
        $strategy = Nat64::localUse();
        $this->assertTrue($strategy->isEmbedded($nonCanonical));
        $this->assertSame($ipv4, $strategy->extract($nonCanonical));
        $this->assertSame($canonical, $strategy->packIntoCanonical($strategy->extract($nonCanonical)));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getInvalidIpAddresses')]
    public function testPackIntoCanonicalThrowsForStringsNot4Bytes(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\PackingException::class);
        $this->strategy->packIntoCanonical($value);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getValidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getValidSequences')]
    public function testPackIntoCanonicalProducesCanonicalForm(string $ipv6, string $ipv4): void
    {
        $this->assertSame($ipv6, $this->strategy->packIntoCanonical($ipv4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getInvalidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getInvalidSequences')]
    public function testPackIntoNonCanonicalThrowsForUnrecognisedIpv6(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\PackingException::class);
        $this->strategy->packIntoNonCanonical($value, \pack('H*', '7f000001'));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getValidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getValidSequences')]
    public function testPackIntoNonCanonicalReportsInvalidIpv4(string $ipv6, string $ipv4): void
    {
        $invalid = \pack('H*', '7f0000');
        $this->expectException(\Darsyn\IP\Exception\Strategy\PackingException::class);
        try {
            $this->strategy->packIntoNonCanonical($ipv6, $invalid);
        } catch (\Darsyn\IP\Exception\Strategy\PackingException $e) {
            $this->assertSame($this->strategy, $e->getEmbeddingStrategy());
            $this->assertSame($invalid, $e->getSuppliedBinary());
            throw $e;
        }
        $this->fail();
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getNonCanonicalNetworkSpecificSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getNonCanonicalNetworkSpecificSequences')]
    public function testPackIntoNonCanonicalPreservesSuffixAndZeroesReservedOctetWithNetworkSpecificPrefixes(string $prefixAddress, int $length, string $nonCanonical, string $ipv4, string $canonical): void
    {
        $strategy = Nat64::networkSpecific(IPv6::fromProtocol($prefixAddress), $length);
        $result = $strategy->packIntoNonCanonical($nonCanonical, $ipv4);
        // The embedded IPv4 round-trips.
        $this->assertSame($ipv4, $strategy->extract($result));
        $bytes = \intdiv($length, 8);
        // The configured prefix is preserved.
        $this->assertSame(\substr($nonCanonical, 0, $bytes), \substr($result, 0, $bytes));
        if (96 !== $length) {
            // The reserved octet (byte 8) is forced to zero.
            $this->assertSame("\0", \substr($result, 8, 1));
            // The suffix (bytes after the embedded address) is preserved from the source.
            $this->assertSame(\substr($nonCanonical, $bytes + 5), \substr($result, $bytes + 5));
        }
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Nat64::getNonCanonicalLocalUseSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(Nat64DataProvider::class, 'getNonCanonicalLocalUseSequences')]
    public function testPackIntoNonCanonicalPreservesSuffixAndZeroesReservedOctetWithLocalUsePrefix(string $nonCanonical, string $ipv4, string $canonical): void
    {
        $strategy = Nat64::localUse();
        $result = $strategy->packIntoNonCanonical($nonCanonical, $ipv4);
        $this->assertSame($ipv4, $strategy->extract($result));
        // Local-use prefix is /48 → 6 bytes, reserved octet at byte 8, suffix from byte 11.
        $this->assertSame(\substr($nonCanonical, 0, 6), \substr($result, 0, 6));
        $this->assertSame("\0", \substr($result, 8, 1));
        $this->assertSame(\substr($nonCanonical, 11), \substr($result, 11));
    }
}
