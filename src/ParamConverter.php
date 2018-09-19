<?php

namespace Darsyn\IP;

use Darsyn\IP\Exception\InvalidIpAddressException;
use Darsyn\IP\Version\Multi as IP;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter as ParamConfig;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ParamConverter implements ParamConverterInterface
{
    /** {@inheritdoc} */
    public function supports(ParamConfig $configuration)
    {
        return $configuration->getClass() === IpInterface::class;
    }

    /** {@inheritdoc} */
    public function apply(Request $request, ParamConfig $configuration)
    {
        $param = $configuration->getName();
        if (!$request->attributes->has($param)) {
            return false;
        }

        $value = $request->attributes->get($param);
        if (!$value && $configuration->isOptional()) {
            $request->attributes->set($param, null);
            return true;
        }

        try {
            $ip = IP::factory($value);
        } catch (InvalidIpAddressException $e) {
            throw new NotFoundHttpException(sprintf(
                'Could not convert request parameter "%s"; invalid IP address.',
                $param
            ), $e);
        }

        $request->attributes->set($param, $ip);
        return true;
    }
}
