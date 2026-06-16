<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\Strategy;

use Darsyn\IP\Strategy\Teredo;
use Darsyn\IP\Tests\DataProvider\Strategy\Teredo as TeredoDataProvider;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class TeredoTest extends TestCase
{
    /** @var \Darsyn\IP\Strategy\CanonicalEmbeddingInterface $strategy */
    private $strategy;

    /** @before */
    #[PHPUnit\Before]
    protected function setUpWithoutReturnDeclaration(): void
    {
        $this->strategy = new Teredo();
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Teredo::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(TeredoDataProvider::class, 'getInvalidIpAddresses')]
    public function testIsEmbeddedReturnsFalseForAStringOtherThan16BytesLong(string $value): void
    {
        $this->assertFalse($this->strategy->isEmbedded($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Teredo::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(TeredoDataProvider::class, 'getValidIpAddresses')]
    public function testIsEmbedded(string $value, bool $isEmbedded): void
    {
        $this->assertSame($isEmbedded, $this->strategy->isEmbedded($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Teredo::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(TeredoDataProvider::class, 'getInvalidIpAddresses')]
    public function testExceptionIsThrownWhenTryingToExtractFromStringsNot16Bytes(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\ExtractionException::class);
        $this->strategy->extract($value);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Teredo::getValidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(TeredoDataProvider::class, 'getValidSequences')]
    public function testCorrectSequenceExtractedFromIpBinary(string $ipv6, string $ipv4): void
    {
        $this->assertSame($ipv4, $this->strategy->extract($ipv6));
    }

    /**
     * @deprecated Covers the deprecated pack(); see `testPackIntoCanonical*` methods.
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Teredo::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(TeredoDataProvider::class, 'getInvalidIpAddresses')]
    public function testExceptionIsThrownWhenTryingToPackStringsNot4Bytes(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\PackingException::class);
        $this->strategy->pack($value);
    }

    /**
     * @deprecated Covers the deprecated pack(); see `testPackIntoCanonical*` methods.
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Teredo::getValidPackSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(TeredoDataProvider::class, 'getValidPackSequences')]
    public function testSequenceCorrectlyPackedIntoIpBinaryFromIpBinary(string $ipv4, string $ipv6): void
    {
        $this->assertSame($ipv6, $this->strategy->pack($ipv4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Teredo::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(TeredoDataProvider::class, 'getInvalidIpAddresses')]
    public function testPackIntoCanonicalThrowsForStringsNot4Bytes(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\PackingException::class);
        $this->strategy->packIntoCanonical($value);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Teredo::getValidPackSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(TeredoDataProvider::class, 'getValidPackSequences')]
    public function testPackIntoCanonicalProducesCanonicalForm(string $ipv4, string $ipv6): void
    {
        $this->assertSame($ipv6, $this->strategy->packIntoCanonical($ipv4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Teredo::getInvalidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(TeredoDataProvider::class, 'getInvalidSequences')]
    public function testPackIntoNonCanonicalThrowsForUnrecognisedIpv6(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\PackingException::class);
        $this->strategy->packIntoNonCanonical($value, \pack('H*', '7f000001'));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Teredo::getValidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(TeredoDataProvider::class, 'getValidSequences')]
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
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Teredo::getValidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(TeredoDataProvider::class, 'getValidSequences')]
    public function testPackIntoNonCanonicalPreservesNonEmbeddedBits(string $ipv6, string $ipv4): void
    {
        $this->assertSame($ipv6, $this->strategy->packIntoNonCanonical($ipv6, $this->strategy->extract($ipv6)));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Teredo::getValidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(TeredoDataProvider::class, 'getValidSequences')]
    public function testPackIntoNonCanonicalEmbedsNewAddress(string $ipv6, string $ipv4): void
    {
        $newV4 = \pack('H*', '08080808');
        $result = $this->strategy->packIntoNonCanonical($ipv6, $newV4);
        $this->assertSame($newV4, $this->strategy->extract($result));
        $this->assertSame(\substr($ipv6, 0, 12), \substr($result, 0, 12));
    }
}
