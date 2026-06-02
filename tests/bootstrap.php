<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase as TestCasePsr4;
use PHPUnit_Framework_TestCase as TestCasePsr1;

require_once __DIR__ . '/../vendor/autoload.php';

if (!class_exists(TestCasePsr4::class) && class_exists(TestCasePsr1::class)) {
    class_alias(TestCasePsr1::class, TestCasePsr4::class);
}
