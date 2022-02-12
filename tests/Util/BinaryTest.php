<?php

namespace Darsyn\IP\Tests\Util;

use Darsyn\IP\Util\Binary;
use PHPUnit\Framework\TestCase;

class BinaryTest extends TestCase
{
    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getInvalidHex()
     */
    public function testInvalidHexInput($input)
    {
        $this->expectException(\InvalidArgumentException::class);
        Binary::fromHex($input);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getInvalidHumanReadable()
     */
    public function testInvalidHumanReadableInput($input)
    {
        $this->expectException(\InvalidArgumentException::class);
        Binary::fromHumanReadable($input);
    }

    /**
     * @test
     */
    public function testEmptyHexInput()
    {
        $this->assertSame('', Binary::fromHex(''));
    }

    /**
     * @test
     */
    public function testEmptyHumanReadableInput()
    {
        $this->assertSame('', Binary::fromHumanReadable(''));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getBinaryData()
     */
    public function testHexCanConvertAndBackAgain($hex, $humanReadable)
    {
        $converted = Binary::fromHex($hex);
        $this->assertSame(strtolower($hex), Binary::toHex($converted));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getBinaryData()
     */
    public function testHumanReadableCanConvertAndBackAgain($hex, $humanReadable)
    {
        $converted = Binary::fromHumanReadable($humanReadable);
        $this->assertSame($humanReadable, Binary::toHumanReadable($converted));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getBinaryData()
     */
    public function testHexCanConvertToHumanReadable($hex, $humanReadable)
    {
        $converted = Binary::fromHex($hex);
        $this->assertSame($humanReadable, Binary::toHumanReadable($converted));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getBinaryData()
     */
    public function testHumanReadableCanConvertToHex($hex, $humanReadable)
    {
        $converted = Binary::fromHumanReadable($humanReadable);
        $this->assertSame(strtolower($hex), Binary::toHex($converted));
    }
}
