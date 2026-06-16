<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\Strategy;

use Darsyn\IP\Strategy\Derived;
use Darsyn\IP\Tests\DataProvider\Strategy\Derived as DerivedDataProvider;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class DerivedTest extends TestCase
{
    /** @var \Darsyn\IP\Strategy\CanonicalEmbeddingInterface $strategy */
    private $strategy;

    /** @before */
    #[PHPUnit\Before]
    protected function setUpWithoutReturnDeclaration(): void
    {
        $this->strategy = new Derived();
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getInvalidIpAddresses')]
    public function testIsEmbeddedReturnsFalseForAStringOtherThan16BytesLong(string $value): void
    {
        $this->assertFalse($this->strategy->isEmbedded($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getValidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getValidIpAddresses')]
    public function testIsEmbedded(string $value, bool $isEmbedded): void
    {
        $this->assertSame($isEmbedded, $this->strategy->isEmbedded($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getInvalidIpAddresses')]
    public function testExceptionIsThrownWhenTryingToExtractFromStringsNot16Bytes(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\ExtractionException::class);
        $this->strategy->extract($value);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getValidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getValidSequences')]
    public function testCorrectSequenceExtractedFromIpBinary(string $ipv6, string $ipv4): void
    {
        $this->assertSame($ipv4, $this->strategy->extract($ipv6));
    }

    /**
     * @deprecated Covers the deprecated pack(); see `testPackIntoCanonical*` methods.
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getInvalidIpAddresses')]
    public function testExceptionIsThrownWhenTryingToPackStringsNot4Bytes(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\PackingException::class);
        $this->strategy->pack($value);
    }

    /**
     * @deprecated Covers the deprecated pack(); see `testPackIntoCanonical*` methods.
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getValidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getValidSequences')]
    public function testSequenceCorrectlyPackedIntoIpBinaryFromIpBinary(string $ipv6, string $ipv4): void
    {
        $this->assertSame($ipv6, $this->strategy->pack($ipv4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getNonCanonicalSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getNonCanonicalSequences')]
    public function testNonCanonical6to4IsEmbedded(string $ipv6, string $ipv4, string $canonical): void
    {
        $this->assertTrue($this->strategy->isEmbedded($ipv6));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getNonCanonicalSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getNonCanonicalSequences')]
    public function testCorrectSequenceExtractedFromNonCanonical6to4(string $ipv6, string $ipv4, string $canonical): void
    {
        $this->assertSame($ipv4, $this->strategy->extract($ipv6));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getNonCanonicalSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getNonCanonicalSequences')]
    public function testNonCanonical6to4CanonicalisedByExtractThenPack(string $ipv6, string $ipv4, string $canonical): void
    {
        $this->assertNotSame($canonical, $ipv6);
        $this->assertSame($canonical, $this->strategy->packIntoCanonical($this->strategy->extract($ipv6)));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getInvalidIpAddresses()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getInvalidIpAddresses')]
    public function testPackIntoCanonicalThrowsForStringsNot4Bytes(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\PackingException::class);
        $this->strategy->packIntoCanonical($value);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getValidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getValidSequences')]
    public function testPackIntoCanonicalProducesCanonicalForm(string $ipv6, string $ipv4): void
    {
        $this->assertSame($ipv6, $this->strategy->packIntoCanonical($ipv4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getInvalidSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getInvalidSequences')]
    public function testPackIntoNonCanonicalThrowsForUnrecognisedIpv6(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\PackingException::class);
        $this->strategy->packIntoNonCanonical($value, \pack('H*', '7f000001'));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getNonCanonicalSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getNonCanonicalSequences')]
    public function testPackIntoNonCanonicalReportsInvalidIpv4(string $ipv6, string $ipv4, string $canonical): void
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
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getNonCanonicalSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getNonCanonicalSequences')]
    public function testPackIntoNonCanonicalPreservesNonEmbeddedBits(string $ipv6, string $ipv4, string $canonical): void
    {
        $this->assertSame($ipv6, $this->strategy->packIntoNonCanonical($ipv6, $ipv4));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getNonCanonicalSequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getNonCanonicalSequences')]
    public function testPackIntoNonCanonicalEmbedsNewAddress(string $ipv6, string $ipv4, string $canonical): void
    {
        $newV4 = \pack('H*', '08080808');
        $result = $this->strategy->packIntoNonCanonical($ipv6, $newV4);
        $this->assertSame($newV4, $this->strategy->extract($result));
        $this->assertSame(\substr($ipv6, 6, 10), \substr($result, 6, 10));
    }
}
