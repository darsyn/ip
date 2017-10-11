<?php

namespace Darsyn\IP\Tests\Doctrine;

use Darsyn\IP\IP;
use Doctrine\DBAL\Types\Type;
use PDO;
use PHPUnit_Framework_TestCase as TestCase;

class IpTypeTest extends TestCase
{
    private $platform;

    /** @var \Darsyn\IP\Doctrine\IpType $type */
    private $type;

    public static function setUpBeforeClass()
    {
        if (class_exists(Type::class)) {
            Type::addType('ip', 'Darsyn\IP\Doctrine\IpType');
        }
    }

    private function getPlatformMock()
    {
        // We have to use MySQL as the platform here, because the AbstractPlatform does not support BINARY types.
        return $this
            ->getMockBuilder('Doctrine\DBAL\Platforms\MySqlPlatform')
            ->setMethods(['getBinaryTypeDeclarationSQL'])
            ->getMockForAbstractClass()
        ;
    }

    protected function setUp()
    {
        if (PHP_INT_SIZE == 4) {
            $this->markTestSkipped('Skipping test that can run only on a 64-bit build of PHP.');
        }
        if (!class_exists('Doctrine\DBAL\Types\Type')) {
            $this->markTestSkipped('Skipping test that requires "doctrine/dbal".');
        }

        $this->platform = $this->getPlatformMock();
        $this->platform
            ->expects($this->any())
            ->method('getBinaryTypeDeclarationSQL')
            ->will($this->returnValue('DUMMYBINARY()'));
        $this->type = Type::getType('ip');
    }

    /**
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::convertToDatabaseValue
     */
    public function testIpConvertsToDatabaseValue()
    {
        $ip = new IP('12.34.56.78');

        $expected = $ip->getBinary();
        $actual = $this->type->convertToDatabaseValue($ip, $this->platform);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::convertToDatabaseValue
     * @expectedException \Doctrine\DBAL\Types\ConversionException
     */
    public function testInvalidIpConversionForDatabaseValue()
    {
        $this->type->convertToDatabaseValue('abcdefg', $this->platform);
    }

    /**
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::convertToDatabaseValue
     */
    public function testNullConversionForDatabaseValue()
    {
        $this->assertNull($this->type->convertToDatabaseValue(null, $this->platform));
    }

    /**
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::convertToPHPValue
     */
    public function testIpConvertsToPHPValue()
    {
        $ip = new IP('12.34.56.78');
        /** @var \Darsyn\IP\IP $dbIp */
        $dbIp = $this->type->convertToPHPValue($ip->getBinary(), $this->platform);
        $this->assertInstanceOf(IP::class, $dbIp);
        $this->assertEquals('12.34.56.78', $dbIp->getProtocolAppropriateAddress());
    }

    /**
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::convertToPHPValue
     */
    public function testIpObjectConvertsToPHPValue()
    {
        $ip = new IP('12.34.56.78');
        /** @var \Darsyn\IP\IP $dbIp */
        $dbIp = $this->type->convertToPHPValue($ip, $this->platform);
        $this->assertInstanceOf(IP::class, $dbIp);
        $this->assertSame($ip, $dbIp);
    }

    /**
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::convertToPHPValue
     */
    public function testStreamConvertsToPHPValue()
    {
        $ip = new IP('12.34.56.78');
        $stream = fopen('php://memory','r+');
        fwrite($stream, $ip->getBinary());
        rewind($stream);
        /** @var \Darsyn\IP\IP $dbIp */
        $dbIp = $this->type->convertToPHPValue($stream, $this->platform);
        $this->assertInstanceOf(IP::class, $dbIp);
        $this->assertEquals('12.34.56.78', $dbIp->getProtocolAppropriateAddress());
    }

    /**
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::convertToPHPValue
     * @expectedException \Doctrine\DBAL\Types\ConversionException
     */
    public function testInvalidIpConversionForPHPValue()
    {
        $this->type->convertToPHPValue('abcdefg', $this->platform);
    }

    /**
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::convertToPHPValue
     */
    public function testNullConversionForPHPValue()
    {
        $this->assertNull($this->type->convertToPHPValue(null, $this->platform));
    }

    /**
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::getName
     */
    public function testGetName()
    {
        $this->assertEquals('ip', $this->type->getName());
    }

    /**
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::getSqlDeclaration
     */
    public function testGetBinaryTypeDeclarationSQL()
    {
        $this->assertEquals('DUMMYBINARY()', $this->type->getSqlDeclaration(['length' => 16], $this->platform));
    }

    /**
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::getBindingType()
     */
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

    /**
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::requiresSQLCommentHint
     */
    public function testRequiresSQLCommentHint()
    {
        $this->assertTrue($this->type->requiresSQLCommentHint($this->platform));
    }
}
