<?php
namespace Darsyn\IP\Tests\Doctrine;

use Darsyn\IP\IP;
use Doctrine\DBAL\Types\Type;
use Doctrine\Tests\DBAL\Mocks\MockPlatform;

class UuidTypeTest extends \PHPUnit_Framework_TestCase
{
    private $platform;

    /**
     * @access private
     * @var \Darsyn\IP\Doctrine\IpType
     */
    private $type;

    public static function setUpBeforeClass()
    {
        if (class_exists('Doctrine\DBAL\Types\Type')) {
            Type::addType('ip', 'Darsyn\IP\Doctrine\IpType');
        }
    }

    /**
     * Get Platform Mock
     *
     * @access private
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPlatformMock()
    {
        return $this
            // We have to use MySQL as the platform here, because the AbstractPlatform does not support BINARY types.
            ->getMockBuilder('Doctrine\DBAL\Platforms\MySqlPlatform')
            ->setMethods(array('getBinaryTypeDeclarationSQL'))
            ->getMockForAbstractClass()
        ;
    }

    /**
     * Test Setup
     *
     * @access protected
     * @return void
     */
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
            ->will($this->returnValue('DUMMYBINARY()'))
        ;
        $this->type = Type::getType('ip');
    }

    /**
     * IP Converts to Database Value
     *
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::convertToDatabaseValue
     * @access public
     * @return void
     */
    public function ipConvertsToDatabaseValue()
    {
        $ip = new IP('12.34.56.78');

        $expected = $ip->getBinary();
        $actual = $this->type->convertToDatabaseValue($ip, $this->platform);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Invalid IP Conversion to Database Value Results in Exception
     *
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::convertToDatabaseValue
     * @expectedException \Doctrine\DBAL\Types\ConversionException
     * @access public
     * @return void
     */
    public function invalidIpConversionForDatabaseValue()
    {
        $this->type->convertToDatabaseValue('abcdefg', $this->platform);
    }

    /**
     * NULL Value Conversation to Database Value
     *
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::convertToDatabaseValue
     * @access public
     * @return void
     */
    public function nullConversionForDatabaseValue()
    {
        $this->assertNull($this->type->convertToDatabaseValue(null, $this->platform));
    }

    /**
     * IP Converts to PHP Value
     *
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::convertToPHPValue
     * @access public
     * @return void
     */
    public function ipConvertsToPHPValue()
    {
        $ip = new IP('12.34.56.78');
        $dbIp = $this->type->convertToPHPValue($ip->getBinary(), $this->platform);
        $this->assertInstanceOf('Darsyn\IP\IP', $dbIp);
        $this->assertEquals('12.34.56.78', $dbIp->getShortAddress());
    }

    /**
     * IP Object Converts to PHP Value
     *
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::convertToPHPValue
     * @access public
     * @return void
     */
    public function ipObjectConvertsToPHPValue()
    {
        $ip = new IP('12.34.56.78');
        $dbIp = $this->type->convertToPHPValue($ip, $this->platform);
        $this->assertInstanceOf('Darsyn\IP\IP', $dbIp);
        $this->assertSame($ip, $dbIp);
    }

    /**
     * Invalid IP Converstion for PHP Value Throws Exception
     *
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::convertToPHPValue
     * @expectedException \Doctrine\DBAL\Types\ConversionException
     * @access public
     * @return void
     */
    public function testInvalidUuidConversionForPHPValue()
    {
        $this->type->convertToPHPValue('abcdefg', $this->platform);
    }

    /**
     * NULL Value Converstion for PHP Value
     *
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::convertToPHPValue
     * @access public
     * @return void
     */
    public function testNullConversionForPHPValue()
    {
        $this->assertNull($this->type->convertToPHPValue(null, $this->platform));
    }

    /**
     * Get Name
     *
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::getName
     * @access public
     * @return void
     */
    public function testGetName()
    {
        $this->assertEquals('ip', $this->type->getName());
    }

    /**
     * Get BINARY Type Declaration SQL
     *
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::getSqlDeclaration
     * @access public
     * @return void
     */
    public function getBinaryTypeDeclarationSQL()
    {
        $this->assertEquals('DUMMYBINARY()', $this->type->getSqlDeclaration(array('length' => 16), $this->platform));
    }

    /**
     * Requires SQL Comment Hint
     *
     * @test
     * @covers \Darsyn\IP\Doctrine\IpType::requiresSQLCommentHint
     * @access public
     * @return void
     */
    public function requiresSQLCommentHint()
    {
        $this->assertTrue($this->type->requiresSQLCommentHint($this->platform));
    }
}
