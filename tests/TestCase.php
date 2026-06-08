<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base test case used for dealing with multi-version inconsistencies.
 */
abstract class TestCase extends BaseTestCase
{
    protected function legacyExpectExceptionMessage(string $contains): void
    {
        // This is what happens when you try to support a stupid number of
        // PHP and PHPUnit versions.
        \method_exists($this, 'expectExceptionMessageIsOrContains')
            // Does not exist on older versions of PHPUnit.
            ? $this->expectExceptionMessageIsOrContains($contains)
            // Throws deprecation warnings on newer versions of PHPUnit.
            : $this->expectExceptionMessage($contains);
    }
}
