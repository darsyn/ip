<?php declare(strict_types=1);

namespace Darsyn\IP\Tests\Formatter;

use Darsyn\IP\Exception\Formatter\FormatException;
use Darsyn\IP\Formatter\NativeFormatter as Formatter;
use Darsyn\IP\Formatter\ProtocolFormatterInterface;
use Darsyn\IP\Tests\TestCase;

class NativeFormatterTest extends TestCase
{
    /** @var \Darsyn\IP\Formatter\ProtocolFormatterInterface $formatter */
    private $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new Formatter;
    }

    /**
     * @test
     */
    public function testFormatterIsInstanceOfInterface(): void
    {
        $this->assertInstanceOf(ProtocolFormatterInterface::class, $this->formatter);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Formatter\NativeFormatter::getValidBinarySequences()
     */
    public function testFormatterReturnsCorrectProtocolString($value, $expected): void
    {
        $this->assertSame($expected, $this->formatter->ntop($value));
    }

    /**
     * @test
     * @expectedException \Darsyn\IP\Exception\Formatter\FormatException
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Formatter\NativeFormatter::getInvalidBinarySequences()
     */
    public function testFormatterThrowsExceptionOnInvalidBinarySequences($value): void
    {
        try {
            $this->formatter->ntop($value);
        } catch (FormatException $e) {
            $this->assertSame($value, $e->getSuppliedBinary());
            throw $e;
        }
        $this->fail();
    }
}
