<?php

namespace Darsyn\IP\Tests\Strategy;

use Darsyn\IP\Exception\Strategy\ExtractionException;
use Darsyn\IP\Exception\Strategy\PackingException;
use Darsyn\IP\Strategy\Compatible;
use Darsyn\IP\Tests\TestCase;

class CompatibleTest extends TestCase
{
    /** @var \Darsyn\IP\Strategy\EmbeddingStrategyInterface $strategy */
    private $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new Compatible;
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Compatible::getInvalidIpAddresses()
     */
    public function testIsEmbeddedReturnsFalseForAStringOtherThan16BytesLong($value)
    {
        $this->assertFalse($this->strategy->isEmbedded($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Compatible::getValidIpAddresses()
     */
    public function testIsEmbedded($value, $isEmbedded)
    {
        $this->assertSame($isEmbedded, $this->strategy->isEmbedded($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Compatible::getInvalidIpAddresses()
     * @expectedException \Darsyn\IP\Exception\Strategy\ExtractionException
     */
    public function testExceptionIsThrownWhenTryingToExtractFromStringsNot16Bytes($value)
    {
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
    public function testCorrectSequenceExtractedFromIpBinary($ipv6, $ipv4)
    {
        $this->assertSame($ipv4, $this->strategy->extract($ipv6));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Compatible::getInvalidIpAddresses()
     * @expectedException \Darsyn\IP\Exception\Strategy\PackingException
     */
    public function testExceptionIsThrownWhenTryingToPackStringsNot4Bytes($value)
    {
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
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Strategy\Compatible::getValidSequences()
     */
    public function testSequenceCorrectlyPackedIntoIpBinaryFromIpBinary($ipv6, $ipv4)
    {
        $this->assertSame($ipv6, $this->strategy->pack($ipv4));
    }
}
