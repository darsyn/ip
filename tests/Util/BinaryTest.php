<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\Util;

use Darsyn\IP\Exception\OverflowException;
use Darsyn\IP\Tests\DataProvider\Util\Binary as BinaryDataProvider;
use Darsyn\IP\Util\Binary;
use Darsyn\IP\Util\MbString;
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

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getDecimalStringData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getDecimalStringData')]
    public function testToDecimalString(string $hex, string $decimal): void
    {
        $this->assertSame($decimal, Binary::toDecimalString(Binary::fromHex($hex)));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getDecimalStringData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getDecimalStringData')]
    public function testFromDecimalString(string $hex, string $decimal): void
    {
        $lengthInBytes = \intdiv(MbString::getLength($hex), 2);
        $this->assertSame($hex, Binary::toHex(Binary::fromDecimalString($decimal, $lengthInBytes)));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getDecimalStringData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getDecimalStringData')]
    public function testDecimalStringRoundTrips(string $hex, string $decimal): void
    {
        $binary = Binary::fromHex($hex);
        $this->assertSame($binary, Binary::fromDecimalString(Binary::toDecimalString($binary), MbString::getLength($binary)));
        $this->assertSame($decimal, Binary::toDecimalString(Binary::fromDecimalString($decimal, 16)));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getInvalidDecimalStrings()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getInvalidDecimalStrings')]
    public function testFromDecimalStringThrowsOnInvalidInput(string $input): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Binary::fromDecimalString($input, 16);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getOverflowDecimalStrings()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getOverflowDecimalStrings')]
    public function testFromDecimalStringThrowsOnOverflow(string $decimal, int $lengthInBytes): void
    {
        $this->expectException(OverflowException::class);
        Binary::fromDecimalString($decimal, $lengthInBytes);
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getEquivalentDecimalStrings()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getEquivalentDecimalStrings')]
    public function testFromDecimalStringAcceptsLeadingZeros(string $padded, string $canonical, int $lengthInBytes): void
    {
        $this->assertSame(
            Binary::fromDecimalString($canonical, $lengthInBytes),
            Binary::fromDecimalString($padded, $lengthInBytes)
        );
    }

    /** @test */
    #[PHPUnit\Test]
    public function testFromDecimalStringZeroProducesZeroedBytes(): void
    {
        $this->assertSame("\x00\x00\x00\x00", Binary::fromDecimalString('0', 4));
        $this->assertSame(\str_repeat("\x00", 16), Binary::fromDecimalString('0', 16));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testToDecimalStringOfEmptyBinaryIsZero(): void
    {
        $this->assertSame('0', Binary::toDecimalString(''));
    }

    /**
     * The public methods take a GMP fast path when the extension is loaded, so
     * the pure-PHP implementations are exercised directly (they are the only
     * runtime path in environments without GMP, such as CI).
     *
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getDecimalStringData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getDecimalStringData')]
    public function testPurePhpToDecimalStringMatchesPublicMethod(string $hex, string $decimal): void
    {
        $binary = Binary::fromHex($hex);
        $this->assertSame($decimal, self::pureToDecimalString($binary));
        $this->assertSame(Binary::toDecimalString($binary), self::pureToDecimalString($binary));
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getDecimalStringData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getDecimalStringData')]
    public function testPurePhpFromDecimalStringMatchesPublicMethod(string $hex, string $decimal): void
    {
        $lengthInBytes = \intdiv(MbString::getLength($hex), 2);
        $this->assertSame(
            Binary::fromDecimalString($decimal, $lengthInBytes),
            MbString::padString(self::pureFromDecimalString($decimal), $lengthInBytes, "\x00", \STR_PAD_LEFT)
        );
    }

    /**
     * @test
     * @dataProvider \Darsyn\IP\Tests\DataProvider\Util\Binary::getDecimalStringData()
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProviderExternal(BinaryDataProvider::class, 'getDecimalStringData')]
    public function testGmpMatchesPurePhpImplementation(string $hex, string $decimal): void
    {
        if (!\extension_loaded('gmp')) {
            self::markTestSkipped('ext-gmp is not loaded.');
        }
        $binary = Binary::fromHex($hex);
        $this->assertSame(self::pureToDecimalString($binary), \gmp_strval(\gmp_import($binary)));
        $this->assertSame(self::pureFromDecimalString($decimal), \gmp_export(\gmp_init($decimal, 10)));
    }

    private static function pureToDecimalString(string $binary): string
    {
        $closure = \Closure::bind(static function (string $binary): string {
            return Binary::toDecimalStringWithoutGmp($binary);
        }, null, Binary::class);
        if (!$closure instanceof \Closure) {
            throw new \RuntimeException('Unable to bind closure to Binary class scope.');
        }
        return $closure($binary);
    }

    private static function pureFromDecimalString(string $decimal): string
    {
        $closure = \Closure::bind(static function (string $decimal): string {
            return Binary::fromDecimalStringWithoutGmp($decimal);
        }, null, Binary::class);
        if (!$closure instanceof \Closure) {
            throw new \RuntimeException('Unable to bind closure to Binary class scope.');
        }
        return $closure($decimal);
    }
}
