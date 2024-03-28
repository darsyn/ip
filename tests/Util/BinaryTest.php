<?php

namespace Darsyn\IP\Tests\Util;

use Darsyn\IP\Tests\DataProvider\Util\Binary as BinaryDataProvider;
use Darsyn\IP\Util\Binary;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class BinaryTest extends TestCase
{
    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getInvalidHex()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getInvalidHex')]
    public function testInvalidHexInput($input)
    {
        $this->expectException(\InvalidArgumentException::class);
        Binary::fromHex($input);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getInvalidHumanReadable()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getInvalidHumanReadable')]
    public function testInvalidHumanReadableInput($input)
    {
        $this->expectException(\InvalidArgumentException::class);
        Binary::fromHumanReadable($input);
    }

    /** @test */
    #[PHPUnit\Test]
    public function testEmptyHexInput()
    {
        $this->assertSame('', Binary::fromHex(''));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testEmptyHumanReadableInput()
    {
        $this->assertSame('', Binary::fromHumanReadable(''));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getBinaryData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getBinaryData')]
    public function testHexCanConvertAndBackAgain($hex, $humanReadableNotUsed)
    {
        $converted = Binary::fromHex($hex);
        $this->assertSame(strtolower($hex), Binary::toHex($converted));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getBinaryData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getBinaryData')]
    public function testHumanReadableCanConvertAndBackAgain($hex, $humanReadable)
    {
        $converted = Binary::fromHumanReadable($humanReadable);
        $this->assertSame($humanReadable, Binary::toHumanReadable($converted));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getBinaryData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getBinaryData')]
    public function testHexCanConvertToHumanReadable($hex, $humanReadable)
    {
        $converted = Binary::fromHex($hex);
        $this->assertSame($humanReadable, Binary::toHumanReadable($converted));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getBinaryData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getBinaryData')]
    public function testHumanReadableCanConvertToHex($hex, $humanReadable)
    {
        $converted = Binary::fromHumanReadable($humanReadable);
        $this->assertSame(strtolower($hex), Binary::toHex($converted));
    }
}
