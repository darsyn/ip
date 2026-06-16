<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\Strategy;

use Darsyn\IP\Exception\Strategy\ExtractionException;
use Darsyn\IP\Exception\Strategy\PackingException;
use Darsyn\IP\Strategy\Compatible;
use Darsyn\IP\Tests\DataProvider\Strategy\Compatible as CompatibleDataProvider;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class CompatibleTest extends TestCase
{
    /** @var \Darsyn\IP\Strategy\CanonicalEmbeddingInterface $strategy */
    private $strategy;

    /** @before */
    #[PHPUnit\Before]
    protected function setUpWithoutReturnDeclaration(): void
    {
        $this->strategy = new Compatible();
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Compatible::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompatibleDataProvider::class, 'getInvalidIpAddresses')]
    public function testIsEmbeddedReturnsFalseForAStringOtherThan16BytesLong(string $value): void
    {
        $this->assertFalse($this->strategy->isEmbedded($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Compatible::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompatibleDataProvider::class, 'getValidIpAddresses')]
    public function testIsEmbedded(string $value, bool $isEmbedded): void
    {
        $this->assertSame($isEmbedded, $this->strategy->isEmbedded($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Compatible::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompatibleDataProvider::class, 'getInvalidIpAddresses')]
    public function testExceptionIsThrownWhenTryingToExtractFromStringsNot16Bytes(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\ExtractionException::class);
        try {
            $this->strategy->extract($value);
        } catch (ExtractionException $e) {
            $this->assertSame($this->strategy, $e->getEmbeddingStrategy());
            $this->assertSame($value, $e->getSuppliedBinary());
            throw $e;
        }
        $this->fail();
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Compatible::getValidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompatibleDataProvider::class, 'getValidSequences')]
    public function testCorrectSequenceExtractedFromIpBinary(string $ipv6, string $ipv4): void
    {
        $this->assertSame($ipv4, $this->strategy->extract($ipv6));
    }

    /**
     * @deprecated Covers the deprecated pack(); see `testPackIntoCanonical*` methods.
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Compatible::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompatibleDataProvider::class, 'getInvalidIpAddresses')]
    public function testExceptionIsThrownWhenTryingToPackStringsNot4Bytes(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\PackingException::class);
        try {
            $this->strategy->pack($value);
        } catch (PackingException $e) {
            $this->assertSame($this->strategy, $e->getEmbeddingStrategy());
            $this->assertSame($value, $e->getSuppliedBinary());
            throw $e;
        }
        $this->fail();
    }

    /**
     * @deprecated Covers the deprecated pack(); see `testPackIntoCanonical*` methods.
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Compatible::getValidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompatibleDataProvider::class, 'getValidSequences')]
    public function testSequenceCorrectlyPackedIntoIpBinaryFromIpBinary(string $ipv6, string $ipv4): void
    {
        $this->assertSame($ipv6, $this->strategy->pack($ipv4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Compatible::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompatibleDataProvider::class, 'getInvalidIpAddresses')]
    public function testPackIntoCanonicalThrowsForStringsNot4Bytes(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\PackingException::class);
        $this->strategy->packIntoCanonical($value);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Compatible::getValidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompatibleDataProvider::class, 'getValidSequences')]
    public function testPackIntoCanonicalProducesCanonicalForm(string $ipv6, string $ipv4): void
    {
        $this->assertSame($ipv6, $this->strategy->packIntoCanonical($ipv4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Compatible::getInvalidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompatibleDataProvider::class, 'getInvalidSequences')]
    public function testPackIntoNonCanonicalThrowsForUnrecognisedIpv6(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\PackingException::class);
        $this->strategy->packIntoNonCanonical($value, \pack('H*', '7f000001'));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Compatible::getValidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompatibleDataProvider::class, 'getValidSequences')]
    public function testPackIntoNonCanonicalEqualsCanonical(string $ipv6, string $ipv4): void
    {
        $this->assertSame($this->strategy->packIntoCanonical($ipv4), $this->strategy->packIntoNonCanonical($ipv6, $ipv4));
    }
}
