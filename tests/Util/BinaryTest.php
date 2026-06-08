<?php

declare(strict_types=1);

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
    public function testInvalidHexInput(string $input): void
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
    public function testInvalidHumanReadableInput(string $input): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Binary::fromHumanReadable($input);
    }

    /** @test */
    #[PHPUnit\Test]
    public function testEmptyHexInput(): void
    {
        $this->assertSame('', Binary::fromHex(''));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testEmptyHumanReadableInput(): void
    {
        $this->assertSame('', Binary::fromHumanReadable(''));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getBinaryData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getBinaryData')]
    public function testHexCanConvertAndBackAgain(string $hex, string $humanReadable): void
    {
        $converted = Binary::fromHex($hex);
        $this->assertSame(\strtolower($hex), Binary::toHex($converted));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getBinaryData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getBinaryData')]
    public function testHumanReadableCanConvertAndBackAgain(string $hex, string $humanReadable): void
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
    public function testHexCanConvertToHumanReadable(string $hex, string $humanReadable): void
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
    public function testHumanReadableCanConvertToHex(string $hex, string $humanReadable): void
    {
        $converted = Binary::fromHumanReadable($humanReadable);
        $this->assertSame(\strtolower($hex), Binary::toHex($converted));
    }
}
