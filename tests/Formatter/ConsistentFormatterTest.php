<?php

namespace Darsyn\IP\Tests\Formatter;

use Darsyn\IP\Exception\Formatter\FormatException;
use Darsyn\IP\Formatter\ConsistentFormatter as Formatter;
use Darsyn\IP\Formatter\ProtocolFormatterInterface;
use Darsyn\IP\Tests\DataProvider\Formatter\ConsistentFormatter as ConsistentFormatterDataProvider;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class ConsistentFormatterTest extends TestCase
{
    /** @var \Darsyn\IP\Formatter\ProtocolFormatterInterface $formatter */
    private $formatter;

    /**
     * @before
     * @return void
     */
    #[PHPUnit\Before]
    protected function setUpWithoutReturnDeclaration()
    {
        $this->formatter = new Formatter;
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testFormatterIsInstanceOfInterface()
    {
        $this->assertInstanceOf(ProtocolFormatterInterface::class, $this->formatter);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Formatter\ConsistentFormatter::getValidBinarySequences()
     * @param string $value
     * @param string $expected
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(ConsistentFormatterDataProvider::class, 'getValidBinarySequences')]
    public function testFormatterReturnsCorrectProtocolString($value, $expected)
    {
        $this->assertSame($expected, $this->formatter->ntop($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Formatter\ConsistentFormatter::getInvalidBinarySequences()
     * @param mixed $value
     * @return void
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(ConsistentFormatterDataProvider::class, 'getInvalidBinarySequences')]
    public function testFormatterThrowsExceptionOnInvalidBinarySequences($value)
    {
        $this->expectException(\Darsyn\IP\Exception\Formatter\FormatException::class);
        try {
            /** @phpstan-ignore-next-line (@phpstan-ignore argument.type) */
            $this->formatter->ntop($value);
        } catch (FormatException $e) {
            $this->assertSame($value, $e->getSuppliedBinary());
            throw $e;
        }
        $this->fail();
    }
}
