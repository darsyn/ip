<?php

namespace Darsyn\IP\Tests\Strategy;

use Darsyn\IP\Strategy\Derived;
use Darsyn\IP\Tests\DataProvider\Strategy\Derived as DerivedDataProvider;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class DerivedTest extends TestCase
{
    /** @var \Darsyn\IP\Strategy\EmbeddingStrategyInterface $strategy */
    private $strategy;

    /**
     * @before
     * @return void
     */
    #[PHPUnit\Before]
    protected function setUpWithoutReturnDeclaration()
    {
        $this->strategy = new Derived;
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getInvalidIpAddresses()
     * @param mixed $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getInvalidIpAddresses')]
    public function testIsEmbeddedReturnsFalseForAStringOtherThan16BytesLong($value)
    {
        /** @phpstan-ignore argument.type */
        $this->assertFalse($this->strategy->isEmbedded($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getValidIpAddresses()
     * @param string $value
     * @param bool $isEmbedded
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getValidIpAddresses')]
    public function testIsEmbedded($value, $isEmbedded)
    {
        $this->assertSame($isEmbedded, $this->strategy->isEmbedded($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getInvalidIpAddresses()
     * @param mixed $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getInvalidIpAddresses')]
    public function testExceptionIsThrownWhenTryingToExtractFromStringsNot16Bytes($value)
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\ExtractionException::class);
        /** @phpstan-ignore argument.type */
        $this->strategy->extract($value);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getValidSequences()
     * @param string $ipv6
     * @param string $ipv4
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getValidSequences')]
    public function testCorrectSequenceExtractedFromIpBinary($ipv6, $ipv4)
    {
        $this->assertSame($ipv4, $this->strategy->extract($ipv6));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getInvalidIpAddresses()
     * @param mixed $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getInvalidIpAddresses')]
    public function testExceptionIsThrownWhenTryingToPackStringsNot4Bytes($value)
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\PackingException::class);
        /** @phpstan-ignore argument.type */
        $this->strategy->pack($value);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Derived::getValidSequences()
     * @param string $ipv6
     * @param string $ipv4
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(DerivedDataProvider::class, 'getValidSequences')]
    public function testSequenceCorrectlyPackedIntoIpBinaryFromIpBinary($ipv6, $ipv4)
    {
        $this->assertSame($ipv6, $this->strategy->pack($ipv4));
    }
}
