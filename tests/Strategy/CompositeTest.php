<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\Strategy;

use Darsyn\IP\Strategy\Composite;
use Darsyn\IP\Strategy\Derived;
use Darsyn\IP\Strategy\Mapped;
use Darsyn\IP\Strategy\Nat64;
use Darsyn\IP\Strategy\Teredo;
use Darsyn\IP\Tests\DataProvider\Strategy\Composite as CompositeDataProvider;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class CompositeTest extends TestCase
{
    /** @var \Darsyn\IP\Strategy\CanonicalEmbeddingInterface $strategy */
    private $strategy;

    /** @before */
    #[PHPUnit\Before]
    protected function setUpWithoutReturnDeclaration(): void
    {
        $this->strategy = new Composite(new Mapped(), new Derived(), Nat64::wellKnown(), new Teredo());
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Composite::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompositeDataProvider::class, 'getInvalidIpAddresses')]
    public function testIsEmbeddedReturnsFalseForAStringOtherThan16BytesLong(string $value): void
    {
        $this->assertFalse($this->strategy->isEmbedded($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Composite::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompositeDataProvider::class, 'getValidIpAddresses')]
    public function testIsEmbedded(string $value, bool $isEmbedded): void
    {
        $this->assertSame($isEmbedded, $this->strategy->isEmbedded($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Composite::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompositeDataProvider::class, 'getInvalidIpAddresses')]
    public function testExceptionIsThrownWhenTryingToExtractFromStringsNot16Bytes(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\ExtractionException::class);
        $this->strategy->extract($value);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Composite::getValidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompositeDataProvider::class, 'getValidSequences')]
    public function testCorrectSequenceExtractedFromIpBinary(string $ipv6, string $ipv4): void
    {
        $this->assertSame($ipv4, $this->strategy->extract($ipv6));
    }

    /**
     * @deprecated Covers the deprecated pack(); see `testPackIntoCanonical*` methods.
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Composite::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompositeDataProvider::class, 'getInvalidIpAddresses')]
    public function testExceptionIsThrownWhenTryingToPackStringsNot4Bytes(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\PackingException::class);
        $this->strategy->pack($value);
    }

    /**
     * @deprecated Covers the deprecated pack(); see `testPackIntoCanonical*` methods.
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Composite::getPackableSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompositeDataProvider::class, 'getPackableSequences')]
    public function testSequenceCorrectlyPackedIntoIpBinaryFromIpBinary(string $ipv6, string $ipv4): void
    {
        $this->assertSame($ipv6, $this->strategy->pack($ipv4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Composite::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompositeDataProvider::class, 'getInvalidIpAddresses')]
    public function testPackIntoCanonicalThrowsForStringsNot4Bytes(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\PackingException::class);
        $this->strategy->packIntoCanonical($value);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Composite::getPackableSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompositeDataProvider::class, 'getPackableSequences')]
    public function testPackIntoCanonicalProducesCanonicalForm(string $ipv6, string $ipv4): void
    {
        $this->assertSame($ipv6, $this->strategy->packIntoCanonical($ipv4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Composite::getInvalidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompositeDataProvider::class, 'getInvalidSequences')]
    public function testPackIntoNonCanonicalThrowsForUnrecognisedIpv6(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\PackingException::class);
        $this->strategy->packIntoNonCanonical($value, \pack('H*', '7f000001'));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Composite::getNonCanonicalDelegationSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompositeDataProvider::class, 'getNonCanonicalDelegationSequences')]
    public function testPackIntoNonCanonicalDelegatesToRecognisingStrategy(string $ipv6, string $ipv4): void
    {
        // Re-embedding the address's own IPv4 reproduces the original (the recognising
        // sub-strategy preserves the non-embedded bits).
        $this->assertSame($ipv6, $this->strategy->packIntoNonCanonical($ipv6, $ipv4));
        $newV4 = \pack('H*', '08080808');
        $this->assertSame($newV4, $this->strategy->extract($this->strategy->packIntoNonCanonical($ipv6, $newV4)));
    }
}
