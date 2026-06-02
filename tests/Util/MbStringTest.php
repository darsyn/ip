<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\Util;

use Darsyn\IP\Util\Binary;
use Darsyn\IP\Util\MbString;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class MbStringTest extends TestCase
{
    public const EMOJI = '😂';
    public const EMOJI_BYTES = 4;
    public const GRAPHEME_CLUSTER = '🧙‍♀️';
    public const GRAPHEME_CLUSTER_BYTES = 13;

    /** @test */
    #[PHPUnit\Test]
    public function testGetLengthAscii(): void
    {
        $this->assertSame(13, MbString::getLength('Hello, World!'));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testGetLengthUnicodeCharacter(): void
    {
        $this->assertSame(7 + self::EMOJI_BYTES, MbString::getLength('Hello! ' . self::EMOJI));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testGetLengthGraphemeCluster(): void
    {
        $this->assertSame(15 + self::GRAPHEME_CLUSTER_BYTES, MbString::getLength('Harriet Potter ' . self::GRAPHEME_CLUSTER));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testSubStringAscii(): void
    {
        $text = 'Hello, World!';
        $substring = MbString::subString($text, 3);
        $this->assertSame(10, MbString::getLength($substring));
        $this->assertSame('lo, World!', $substring);
    }

    /** @test */
    #[PHPUnit\Test]
    public function testSubStringUnicodeCharacter(): void
    {
        $text = 'Hello! ' . self::EMOJI;
        $substring = MbString::subString($text, 5, 4);
        $this->assertSame(4, MbString::getLength($substring));
        $this->assertSame('! ' . Binary::fromHex('f09f'), $substring);
    }

    /** @test */
    #[PHPUnit\Test]
    public function testSubStringGraphemeCluster(): void
    {
        $text = 'Harriet Potter ' . self::GRAPHEME_CLUSTER;
        $substring = MbString::subString($text, 11, 10);
        $this->assertSame(10, MbString::getLength($substring));
        $this->assertSame('ter ' . Binary::fromHex('f09fa799e280'), $substring);
    }

    /** @test */
    #[PHPUnit\Test]
    public function testSubStringPreservesZeroByte(): void
    {
        $this->assertSame('0', MbString::subString('0', 0, 1));
        $this->assertSame('0', MbString::subString('0', 0));
        $this->assertSame('0', MbString::subString('109', 1, 1));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testPadStringAscii(): void
    {
        $this->assertSame('-0--Hello', $result = MbString::padString('Hello', 9, '-0-', STR_PAD_LEFT));
        $this->assertSame('Hello-0--', $result = MbString::padString('Hello', 9, '-0-', STR_PAD_RIGHT));
        $this->assertSame('-0Hello-0', $result = MbString::padString('Hello', 9, '-0-', STR_PAD_BOTH));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testPadStringUnicodeCharacter(): void
    {
        $this->assertSame('---' . self::EMOJI, MbString::padString(self::EMOJI, 3 + self::EMOJI_BYTES, '-', STR_PAD_LEFT));
        $this->assertSame(self::EMOJI . '---', MbString::padString(self::EMOJI, 3 + self::EMOJI_BYTES, '-', STR_PAD_RIGHT));
        $this->assertSame('-' . self::EMOJI . '--', MbString::padString(self::EMOJI, 3 + self::EMOJI_BYTES, '-', STR_PAD_BOTH));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testPadStringGraphemeCluster(): void
    {
        $this->assertSame('--' . self::GRAPHEME_CLUSTER, MbString::padString(self::GRAPHEME_CLUSTER, 2 + self::GRAPHEME_CLUSTER_BYTES, '-', STR_PAD_LEFT));
        $this->assertSame(self::GRAPHEME_CLUSTER . '--', MbString::padString(self::GRAPHEME_CLUSTER, 2 + self::GRAPHEME_CLUSTER_BYTES, '-', STR_PAD_RIGHT));
        $this->assertSame('-' . self::GRAPHEME_CLUSTER . '-', MbString::padString(self::GRAPHEME_CLUSTER, 2 + self::GRAPHEME_CLUSTER_BYTES, '-', STR_PAD_BOTH));
    }
}
