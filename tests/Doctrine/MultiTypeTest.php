<?php

namespace Darsyn\IP\Tests\Doctrine;

use Darsyn\IP\Doctrine\MultiType;
use Darsyn\IP\Version\Multi as IP;
use Doctrine\DBAL\Types\Type;
use PDO;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class MultiTypeTest extends TestCase
{
    /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
    private $platform;

    /** @var \Darsyn\IP\Doctrine\MultiType $type */
    private $type;

    /** @beforeClass */
    #[PHPUnit\BeforeClass]
    public static function setUpBeforeClassWithoutReturnDeclaration()
    {
        if (class_exists(Type::class)) {
            Type::addType('ip_multi', MultiType::class);
        }
    }

    /** @before */
    #[PHPUnit\Before]
    protected function setUpWithoutReturnDeclaration()
    {
        if (!class_exists('Doctrine\DBAL\Types\Type')) {
            $this->markTestSkipped('Skipping test that requires "doctrine/dbal".');
        }

        $this->platform = new TestPlatform;
        $this->type = Type::getType('ip_multi');
    }

    /** @test */
    #[PHPUnit\Test]
    public function testIpConvertsToDatabaseValue()
    {
        $ip = IP::factory('12.34.56.78');

        $expected = $ip->getBinary();
        $actual = $this->type->convertToDatabaseValue($ip, $this->platform);

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    #[PHPUnit\Test]
    public function testInvalidIpConversionForDatabaseValue()
    {
        $this->expectException(\Doctrine\DBAL\Types\ConversionException::class);
        $this->type->convertToDatabaseValue('abcdefg', $this->platform);
    }

    /** @test */
    #[PHPUnit\Test]
    public function testNullConversionForDatabaseValue()
    {
        $this->assertNull($this->type->convertToDatabaseValue(null, $this->platform));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testIpConvertsToPHPValue()
    {
        $ip = IP::factory('12.34.56.78');
        /** @var IP $dbIp */
        $dbIp = $this->type->convertToPHPValue($ip->getBinary(), $this->platform);
        $this->assertInstanceOf(IP::class, $dbIp);
        $this->assertEquals('12.34.56.78', $dbIp->getDotAddress());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testIpObjectConvertsToPHPValue()
    {
        $ip = IP::factory('12.34.56.78');
        /** @var IP $dbIp */
        $dbIp = $this->type->convertToPHPValue($ip, $this->platform);
        $this->assertInstanceOf(IP::class, $dbIp);
        $this->assertSame($ip, $dbIp);
    }

    /** @test */
    #[PHPUnit\Test]
    public function testStreamConvertsToPHPValue()
    {
        $ip = IP::factory('12.34.56.78');
        $stream = fopen('php://memory','r+');
        fwrite($stream, $ip->getBinary());
        rewind($stream);
        /** @var IP $dbIp */
        $dbIp = $this->type->convertToPHPValue($stream, $this->platform);
        $this->assertInstanceOf(IP::class, $dbIp);
        $this->assertEquals('12.34.56.78', $dbIp->getDotAddress());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testInvalidIpConversionForPHPValue()
    {
        $this->expectException(\Doctrine\DBAL\Types\ConversionException::class);
        $this->type->convertToPHPValue('abcdefg', $this->platform);
    }

    /** @test */
    #[PHPUnit\Test]
    public function testNullConversionForPHPValue()
    {
        $this->assertNull($this->type->convertToPHPValue(null, $this->platform));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testGetName()
    {
        $this->assertEquals('ip', $this->type->getName());
    }

    /** @test */
    #[PHPUnit\Test]
    public function testGetBinaryTypeDeclarationSQL()
    {
        $this->assertEquals('DUMMYBINARY()', $this->type->getSqlDeclaration(['length' => 16], $this->platform));
    }

    /** @test */
    #[PHPUnit\Test]
    public function testBindingTypeIsAValidPDOTypeConstant()
    {
        // Get all constants of the PDO class.
        $constants = (new \ReflectionClass(PDO::class))->getConstants();
        // Now filter out any constants that don't begin with "PARAM_".
        $paramConstants = array_intersect_key(
            $constants,
            array_flip(array_filter(array_keys($constants), function ($key) {
                return strpos($key, 'PARAM_') === 0;
            }))
        );
        // Check that the return value of the Type's binding value is a valid
        // PDO PARAM constant.
        $this->assertContains($this->type->getBindingType(), $paramConstants);
    }

    /** @test */
    #[PHPUnit\Test]
    public function testRequiresSQLCommentHint()
    {
        $this->assertTrue($this->type->requiresSQLCommentHint($this->platform));
    }
}
