<?php

use PHPUnit_Framework_TestCase as TestCasePsr1;
use PHPUnit\Framework\TestCase as TestCasePsr4;

require_once __DIR__ . '/../vendor/autoload.php';

if (!class_exists(TestCasePsr4::class) && class_exists(TestCasePsr1::class)) {
	class_alias(TestCasePsr1::class, TestCasePsr4::class);
}
