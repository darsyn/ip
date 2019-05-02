<?php
declare(strict_types=1);

namespace Darsyn\IP\Tests\Formatter;

use Darsyn\IP\Exception\Formatter\FormatException;
use Darsyn\IP\Formatter\ExpandedIpV6Formatter as Formatter;
use Darsyn\IP\Formatter\ProtocolFormatterInterface;
use Darsyn\IP\Tests\TestCase;

class ExpandedIpV6FormatterTest extends TestCase
{
    /** @var \Darsyn\IP\Formatter\ProtocolFormatterInterface $formatter */
    private $formatter;

    protected function setUp()
    {
        parent::setUp();
        $this->formatter = new Formatter;
    }

    /**
     * @test
     */
    public function testFormatterIsInstanceOfInterface()
    {
        $this->assertInstanceOf(ProtocolFormatterInterface::class, $this->formatter);
    }

    /**
     * @test
     * @expectedException \Darsyn\IP\Exception\Formatter\FormatException
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Formatter\ConsistentFormatter::getInvalidBinarySequences()
     */
    public function testFormatterThrowsExceptionOnInvalidBinarySequences($value)
    {
        try {
            $this->formatter->ntop($value);
        } catch (FormatException $e) {
            $this->assertSame($value, $e->getSuppliedBinary());
            throw $e;
        }
        $this->fail();
    }

    public function testFormatterExapndsIpV6Address()
    {
        $ip = pack('H*', '20010db8000000000a608a2e03707334'); // 2001:db8::a60:8a2e:370:7334
        $this->assertEquals('2001:0db8:0000:0000:0a60:8a2e:0370:7334', $this->formatter->ntop($ip));
    }
}
