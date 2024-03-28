<?php

namespace Darsyn\IP\Tests\Util;

use Darsyn\IP\Util\Binary;
use Darsyn\IP\Util\MbString;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class MbStringTest extends TestCase
{
    const EMOJI = '😂';
    const EMOJI_BYTES = 4;

    const GRAPHEME_CLUSTER = '🧙‍♀️';
    const GRAPHEME_CLUSTER_BYTES = 13;

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testGetLengthAscii()
    {
        $this->assertSame(13, MbString::getLength('Hello, World!'));
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testGetLengthUnicodeCharacter()
    {
        $this->assertSame(7 + self::EMOJI_BYTES, MbString::getLength('Hello! ' . self::EMOJI));
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testGetLengthGraphemeCluster()
    {
        $this->assertSame(15 + self::GRAPHEME_CLUSTER_BYTES, MbString::getLength('Harriet Potter ' . self::GRAPHEME_CLUSTER));
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testSubStringAscii()
    {
        $text = 'Hello, World!';
        $substring = MbString::subString($text, 3);
        $this->assertSame(10, MbString::getLength($substring));
        $this->assertSame('lo, World!', $substring);
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testSubStringUnicodeCharacter()
    {
        $text = 'Hello! ' . self::EMOJI;
        $substring = MbString::subString($text, 5, 4);
        $this->assertSame(4, MbString::getLength($substring));
        $this->assertSame('! ' . Binary::fromHex('f09f'), $substring);
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testSubStringGraphemeCluster()
    {
        $text = 'Harriet Potter ' . self::GRAPHEME_CLUSTER;
        $substring = MbString::subString($text, 11, 10);
        $this->assertSame(10, MbString::getLength($substring));
        $this->assertSame('ter ' . Binary::fromHex('f09fa799e280'), $substring);
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testPadStringAscii()
    {
        $this->assertSame('-0--Hello', $result = MbString::padString('Hello', 9, '-0-', STR_PAD_LEFT));
        $this->assertSame('Hello-0--', $result = MbString::padString('Hello', 9, '-0-', STR_PAD_RIGHT));
        $this->assertSame('-0Hello-0', $result = MbString::padString('Hello', 9, '-0-', STR_PAD_BOTH));
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testPadStringUnicodeCharacter()
    {
        $this->assertSame('---' . self::EMOJI, MbString::padString(self::EMOJI, 3 + self::EMOJI_BYTES, '-', STR_PAD_LEFT));
        $this->assertSame(self::EMOJI . '---', MbString::padString(self::EMOJI, 3 + self::EMOJI_BYTES, '-', STR_PAD_RIGHT));
        $this->assertSame('-' . self::EMOJI . '--', MbString::padString(self::EMOJI, 3 + self::EMOJI_BYTES, '-', STR_PAD_BOTH));
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testPadStringGraphemeCluster()
    {
        $this->assertSame('--' . self::GRAPHEME_CLUSTER, MbString::padString(self::GRAPHEME_CLUSTER, 2 + self::GRAPHEME_CLUSTER_BYTES, '-', STR_PAD_LEFT));
        $this->assertSame(self::GRAPHEME_CLUSTER . '--', MbString::padString(self::GRAPHEME_CLUSTER, 2 + self::GRAPHEME_CLUSTER_BYTES, '-', STR_PAD_RIGHT));
        $this->assertSame('-' . self::GRAPHEME_CLUSTER . '-', MbString::padString(self::GRAPHEME_CLUSTER, 2 + self::GRAPHEME_CLUSTER_BYTES, '-', STR_PAD_BOTH));
    }
}
