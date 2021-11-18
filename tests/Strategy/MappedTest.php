<?php

namespace Darsyn\IP\Tests\Strategy;

use Darsyn\IP\Strategy\Mapped;
use PHPUnit\Framework\TestCase;

class MappedTest extends TestCase
{
    /** @var \Darsyn\IP\Strategy\EmbeddingStrategyInterface $strategy */
    private $strategy;

    /** @before */
    protected function setUpWithoutReturnDeclaration()
    {
        $this->strategy = new Mapped;
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Mapped::getInvalidIpAddresses()
     */
    public function testIsEmbeddedReturnsFalseForAStringOtherThan16BytesLong($value)
    {
        $this->assertFalse($this->strategy->isEmbedded($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Mapped::getValidIpAddresses()
     */
    public function testIsEmbedded($value, $isEmbedded)
    {
        $this->assertSame($isEmbedded, $this->strategy->isEmbedded($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Mapped::getInvalidIpAddresses()
     */
    public function testExceptionIsThrownWhenTryingToExtractFromStringsNot16Bytes($value)
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\ExtractionException::class);
        $this->strategy->extract($value);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Mapped::getValidSequences()
     */
    public function testCorrectSequenceExtractedFromIpBinary($ipv6, $ipv4)
    {
        $this->assertSame($ipv4, $this->strategy->extract($ipv6));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Mapped::getInvalidIpAddresses()
     */
    public function testExceptionIsThrownWhenTryingToPackStringsNot4Bytes($value)
    {
        $this->expectException(\Darsyn\IP\Exception\Strategy\PackingException::class);
        $this->strategy->pack($value);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Mapped::getValidSequences()
     */
    public function testSequenceCorrectlyPackedIntoIpBinaryFromIpBinary($ipv6, $ipv4)
    {
        $this->assertSame($ipv6, $this->strategy->pack($ipv4));
    }
}
