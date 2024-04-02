<?php

namespace Darsyn\IP\Tests\Strategy;

use Darsyn\IP\Exception\Strategy\ExtractionException;
use Darsyn\IP\Exception\Strategy\PackingException;
use Darsyn\IP\Strategy\Compatible;
use Darsyn\IP\Tests\DataProvider\Strategy\Compatible as CompatibleDataProvider;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class CompatibleTest extends TestCase
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
        $this->strategy = new Compatible;
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Compatible::getInvalidIpAddresses()
     * @param mixed $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompatibleDataProvider::class, 'getInvalidIpAddresses')]
    public function testIsEmbeddedReturnsFalseForAStringOtherThan16BytesLong($value)
    {
        /** @phpstan-ignore-next-line (@phpstan-ignore argument.type) */
        $this->assertFalse($this->strategy->isEmbedded($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Compatible::getValidIpAddresses()
     * @param string $value
     * @param bool $isEmbedded
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompatibleDataProvider::class, 'getValidIpAddresses')]
    public function testIsEmbedded($value, $isEmbedded)
    {
        $this->assertSame($isEmbedded, $this->strategy->isEmbedded($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Compatible::getInvalidIpAddresses()
     * @param mixed $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompatibleDataProvider::class, 'getInvalidIpAddresses')]
    public function testExceptionIsThrownWhenTryingToExtractFromStringsNot16Bytes($value)
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\ExtractionException::class);
        try {
            /** @phpstan-ignore-next-line (@phpstan-ignore argument.type) */
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
     * @param string $ipv6
     * @param string $ipv4
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompatibleDataProvider::class, 'getValidSequences')]
    public function testCorrectSequenceExtractedFromIpBinary($ipv6, $ipv4)
    {
        $this->assertSame($ipv4, $this->strategy->extract($ipv6));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Compatible::getInvalidIpAddresses()
     * @param mixed $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompatibleDataProvider::class, 'getInvalidIpAddresses')]
    public function testExceptionIsThrownWhenTryingToPackStringsNot4Bytes($value)
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\PackingException::class);
        try {
            /** @phpstan-ignore-next-line (@phpstan-ignore argument.type) */
            $this->strategy->pack($value);
        } catch (PackingException $e) {
            $this->assertSame($this->strategy, $e->getEmbeddingStrategy());
            $this->assertSame($value, $e->getSuppliedBinary());
            throw $e;
        }
        $this->fail();
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Compatible::getValidSequences()
     * @param string $ipv6
     * @param string $ipv4
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(CompatibleDataProvider::class, 'getValidSequences')]
    public function testSequenceCorrectlyPackedIntoIpBinaryFromIpBinary($ipv6, $ipv4)
    {
        $this->assertSame($ipv6, $this->strategy->pack($ipv4));
    }
}
