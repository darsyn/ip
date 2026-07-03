<?php

declare(strict_types=1);

namespace Darsyn\IP\Version;

use Darsyn\IP\Contracts\Classification6Interface;
use Darsyn\IP\Contracts\FactoryInterface;
use Darsyn\IP\Contracts\Output6Interface;
use Darsyn\IP\Contracts\StrategyDetectionInterface;
use Darsyn\IP\IpInterface;

interface Version6Interface extends IpInterface, Classification6Interface, Output6Interface, FactoryInterface, StrategyDetectionInterface {}
