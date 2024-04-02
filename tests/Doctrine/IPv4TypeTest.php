<?php

namespace Darsyn\IP\Tests\Doctrine;

use Darsyn\IP\Doctrine\IPv4Type;
use Darsyn\IP\Version\IPv4 as IP;
use Doctrine\DBAL\Types\Type;
use PDO;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class IPv4TypeTest extends TestCase
{
    /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
    private $platform;

    /** @var \Darsyn\IP\Doctrine\IPv4Type $type */
    private $type;

    /**
     * @beforeClass
     * @return void
     */
    #[PHPUnit\BeforeClass]
    public static function setUpBeforeClassWithoutReturnDeclaration()
    {
        if (class_exists(Type::class)) {
            Type::addType('ipv4', IPv4Type::class);
        }
    }

    /**
     * @before
     * @return void
     */
    #[PHPUnit\Before]
    protected function setUpWithoutReturnDeclaration()
    {
        if (!class_exists('Doctrine\DBAL\Types\Type')) {
            $this->markTestSkipped('Skipping test that requires "doctrine/dbal".');
        }

        $this->platform = new TestPlatform;
        $type = Type::getType('ipv4');
        $this->assertInstanceOf(IPv4Type::class, $type);
        $this->type = $type;
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testIpConvertsToDatabaseValue()
    {
        $ip = IP::factory('12.34.56.78');

        $expected = $ip->getBinary();
        $actual = $this->type->convertToDatabaseValue($ip, $this->platform);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testInvalidIpConversionForDatabaseValue()
    {
        $this->expectException(\Doctrine\DBAL\Types\ConversionException::class);
        $this->type->convertToDatabaseValue('abcdefg', $this->platform);
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testNullConversionForDatabaseValue()
    {
        $this->assertNull($this->type->convertToDatabaseValue(null, $this->platform));
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testIpConvertsToPHPValue()
    {
        $ip = IP::factory('12.34.56.78');
        /** @var IP $dbIp */
        $dbIp = $this->type->convertToPHPValue($ip->getBinary(), $this->platform);
        $this->assertInstanceOf(IP::class, $dbIp);
        $this->assertEquals('12.34.56.78', $dbIp->getDotAddress());
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testIpObjectConvertsToPHPValue()
    {
        $ip = IP::factory('12.34.56.78');
        /** @var IP $dbIp */
        $dbIp = $this->type->convertToPHPValue($ip, $this->platform);
        $this->assertInstanceOf(IP::class, $dbIp);
        $this->assertSame($ip, $dbIp);
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testStreamConvertsToPHPValue()
    {
        $ip = IP::factory('12.34.56.78');
        $stream = fopen('php://memory','r+');
        // assertIsResource() isn't available for PHP 5.6 and 7.0 (PHPUnit < 7.0).
        $this->assertTrue(is_resource($stream));
        fwrite($stream, $ip->getBinary());
        rewind($stream);
        /** @var IP $dbIp */
        $dbIp = $this->type->convertToPHPValue($stream, $this->platform);
        $this->assertInstanceOf(IP::class, $dbIp);
        $this->assertEquals('12.34.56.78', $dbIp->getDotAddress());
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testInvalidIpConversionForPHPValue()
    {
        $this->expectException(\Doctrine\DBAL\Types\ConversionException::class);
        $this->type->convertToPHPValue('abcdefg', $this->platform);
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testNullConversionForPHPValue()
    {
        $this->assertNull($this->type->convertToPHPValue(null, $this->platform));
    }

    /**
     * @test
     * @return void
     */
    #[PHPUnit\Test]
    public function testGetBinaryTypeDeclarationSQL()
    {
        $this->assertEquals('DUMMYBINARY()', $this->type->getSQLDeclaration(['length' => 4], $this->platform));
    }

    /**
     * @test
     * @return void
     */
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
}
