<?php

namespace Darsyn\IP\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp()
    {
        if (PHP_INT_SIZE == 4) {
            $this->markTestSkipped('Skipping test that can run only on a 64-bit build of PHP.');
        }
    }
}
