<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\Util;

use Darsyn\IP\Exception\OverflowException;
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

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getIncrementData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getIncrementData')]
    public function testIncrement(string $input, string $expected): void
    {
        $this->assertSame($expected, Binary::toHex(Binary::increment(Binary::fromHex($input))));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getDecrementData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getDecrementData')]
    public function testDecrement(string $input, string $expected): void
    {
        $this->assertSame($expected, Binary::toHex(Binary::decrement(Binary::fromHex($input))));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getOffsetData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getOffsetData')]
    public function testAddIntegerOffset(string $input, int $offset, string $expected): void
    {
        $this->assertSame($expected, Binary::toHex(Binary::addIntegerOffset(Binary::fromHex($input), $offset)));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getIncrementOverflowData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getIncrementOverflowData')]
    public function testIncrementOverflowThrows(string $input): void
    {
        $this->expectException(OverflowException::class);
        Binary::increment(Binary::fromHex($input));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getDecrementUnderflowData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getDecrementUnderflowData')]
    public function testDecrementUnderflowThrows(string $input): void
    {
        $this->expectException(OverflowException::class);
        Binary::decrement(Binary::fromHex($input));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getOffsetOverflowData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getOffsetOverflowData')]
    public function testAddIntegerOffsetOverflowThrows(string $input, int $offset): void
    {
        $this->expectException(OverflowException::class);
        Binary::addIntegerOffset(Binary::fromHex($input), $offset);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getBinaryData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getBinaryData')]
    public function testArithmeticRoundTrips(string $hex, string $humanReadable): void
    {
        $binary = Binary::fromHex($hex);
        $this->assertSame($binary, Binary::decrement(Binary::increment($binary)));
        $this->assertSame($binary, Binary::increment(Binary::decrement($binary)));
        $this->assertSame($binary, Binary::addIntegerOffset($binary, 0));
    }
}
