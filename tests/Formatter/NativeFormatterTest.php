<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\Formatter;

use Darsyn\IP\Exception\Formatter\FormatException;
use Darsyn\IP\Formatter\NativeFormatter as Formatter;
use Darsyn\IP\Formatter\ProtocolFormatterInterface;
use Darsyn\IP\Tests\DataProvider\Formatter\NativeFormatter as NativeFormatterDataProvider;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class NativeFormatterTest extends TestCase
{
    /** @var \Darsyn\IP\Formatter\ProtocolFormatterInterface $formatter */
    private $formatter;

    /** @before */
    #[PHPUnit\Before]
    protected function setUpWithoutReturnDeclaration(): void
    {
        $this->formatter = new Formatter();
    }

    /** @test */
    #[PHPUnit\Test]
    public function testFormatterIsInstanceOfInterface(): void
    {
        $this->assertInstanceOf(ProtocolFormatterInterface::class, $this->formatter);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Formatter\NativeFormatter::getValidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(NativeFormatterDataProvider::class, 'getValidBinarySequences')]
    public function testFormatterReturnsCorrectProtocolString(string $value, string $expected): void
    {
        $this->assertSame($expected, $this->formatter->ntop($value));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Formatter\NativeFormatter::getInvalidBinarySequences()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(NativeFormatterDataProvider::class, 'getInvalidBinarySequences')]
    public function testFormatterThrowsExceptionOnInvalidBinarySequences(string $value): void
    {
        $this->expectException(\Darsyn\IP\Exception\Formatter\FormatException::class);
        try {
            $this->formatter->ntop($value);
        } catch (FormatException $e) {
            $this->assertSame($value, $e->getSuppliedBinary());
            throw $e;
        }
        $this->fail();
    }
}
