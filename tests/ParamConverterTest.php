<?php

namespace Darsyn\IP\Tests;

use Darsyn\IP\Exception\IpException;
use Darsyn\IP\IpInterface;
use Darsyn\IP\ParamConverter;
use Darsyn\IP\Version\Multi as IP;
use Darsyn\IP\Version\MultiVersionInterface;
use Mockery as m;
use Mockery\MockInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter as ParamConfig;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ParamConverterTest extends TestCase
{
    /** @var MockInterface|Request $request */
    private $request;
    /** @var \Darsyn\IP\ParamConverter $converter */
    private $converter;

    public function setUp()
    {
        parent::setUp();
        $this->converter = new ParamConverter;
    }

    /** @test */
    public function testConverterSupportsIpInterface()
    {
        /** @var MockInterface|ParamConverter $paramConfig */
        $paramConfig = m::mock(ParamConfig::class);
        $paramConfig->shouldReceive('getClass')->once()->andReturn(IpInterface::class);
        $result = $this->converter->supports($paramConfig);
        $this->assertTrue($result);
    }

    /** @test */
    public function testConverterDoesNotSupportChildInterfaces()
    {
        /** @var MockInterface|ParamConfig $paramConfig */
        $paramConfig = m::mock(ParamConfig::class);
        $paramConfig->shouldReceive('getClass')->once()->andReturn(MultiVersionInterface::class);
        $result = $this->converter->supports($paramConfig);
        $this->assertFalse($result);
    }

    /** @test */
    public function testConverterDoesNotSupportConcreteClasses()
    {
        /** @var MockInterface|ParamConfig $paramConfig */
        $paramConfig = m::mock(ParamConfig::class);
        $paramConfig->shouldReceive('getClass')->once()->andReturn(IP::class);
        $result = $this->converter->supports($paramConfig);
        $this->assertFalse($result);
    }

    /** @test */
    public function testConverterDoesNotSupportNonTypeHintedParameters()
    {
        /** @var MockInterface|ParamConfig $paramConfig */
        $paramConfig = m::mock(ParamConfig::class);
        $paramConfig->shouldReceive('getClass')->once()->andReturn(null);
        $result = $this->converter->supports($paramConfig);
        $this->assertFalse($result);
    }

    /**
     * @return \Mockery\MockInterface|\Symfony\Component\HttpFoundation\ParameterBag
     */
    private function configureRequestAttributes()
    {
        $this->request = m::mock(Request::class);
        $attributes = m::mock(ParameterBag::class);
        $this->request->attributes = $attributes;
        return $attributes;
    }

    /** @test */
    public function testConverterAbstainsWhenParameterIsNotPresentInRequest()
    {
        $attributes = $this->configureRequestAttributes();
        $attributes->shouldReceive('has')->once()->with('ip')->andReturn(false);
        /** @var MockInterface|ParamConfig $paramConfig */
        $paramConfig = m::mock(ParamConfig::class);
        $paramConfig->shouldReceive('getName')->once()->andReturn('ip');
        $result = $this->converter->apply($this->request, $paramConfig);
        $this->assertFalse($result);
    }

    /** @test */
    public function testAttributeIsUnsetIsEmptyAndOptional()
    {
        $attributes = $this->configureRequestAttributes();
        $attributes->shouldReceive('has')->once()->with('ip')->andReturn(true);
        $attributes->shouldReceive('get')->once()->with('ip')->andReturn('');
        $attributes->shouldReceive('set')->once()->with('ip', null);
        /** @var MockInterface|ParamConfig $paramConfig */
        $paramConfig = m::mock(ParamConfig::class);
        $paramConfig->shouldReceive('getName')->once()->andReturn('ip');
        $paramConfig->shouldReceive('isOptional')->once()->andReturn(true);
        $result = $this->converter->apply($this->request, $paramConfig);
        $this->assertTrue($result);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testInvalidValueCausesNotFoundException()
    {
        $attributes = $this->configureRequestAttributes();
        $attributes->shouldReceive('has')->once()->with('ip')->andReturn(true);
        $attributes->shouldReceive('get')->once()->with('ip')->andReturn('invalid');
        $attributes->shouldReceive('set')->once()->with('ip', null);
        /** @var MockInterface|ParamConfig $paramConfig */
        $paramConfig = m::mock(ParamConfig::class);
        $paramConfig->shouldReceive('getName')->once()->andReturn('ip');
        try {
            $this->converter->apply($this->request, $paramConfig);
        } catch (NotFoundHttpException $e) {
            $this->assertInstanceOf(IpException::class, $e->getPrevious());
            throw $e;
        }
    }

    /** @test */
    public function testValidIpAddressCausesObjectToBeSetAsRequestAttribute()
    {
        $attributes = $this->configureRequestAttributes();
        $attributes->shouldReceive('has')->once()->with('ip')->andReturn(true);
        $attributes->shouldReceive('get')->once()->with('ip')->andReturn('12.34.56.78');
        $attributes->shouldReceive('set')->once()->with('ip', m::type(IP::class));
        /** @var MockInterface|ParamConfig $paramConfig */
        $paramConfig = m::mock(ParamConfig::class);
        $paramConfig->shouldReceive('getName')->once()->andReturn('ip');
        $result = $this->converter->apply($this->request, $paramConfig);
        $this->assertTrue($result);
    }
}
