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

    /**
     * This is what happens when you try to be smart and trigger
     * E_USER_DEPRECATED like Symfony does. Be backwards compatible they said,
     * it'll be fun they said.
     */
    protected function captureDeprecation(callable $callback): ?string
    {
        $message = null;
        \set_error_handler(static function (int $errno, string $errstr) use (&$message): bool {
            $message = $errstr;
            return true;
        }, \E_USER_DEPRECATED);
        try {
            $callback();
        } finally {
            \restore_error_handler();
        }
        return $message;
    }
}
