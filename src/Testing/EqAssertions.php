<?php

namespace thgs\Functional\Testing;

use thgs\Functional\Typeclass\EqInstance;

trait EqAssertions
{
    public function assertImplementsEqCorrectly(EqInstance $instance, EqInstance $otherInstance): void
    {
        $this->assertTrue($instance->equals($instance));
        $this->assertTrue($instance->equals(clone $instance));
        
        $this->assertTrue($instance->notEquals($otherInstance));
        $this->assertTrue($instance->notEquals(clone $otherInstance));
    }

    /**
     * Asserts that a condition is true.
     *
     * @throws ExpectationFailedException
     *
     * @psalm-assert true $condition
     */
    abstract public static function assertTrue(mixed $condition, string $message = ''): void;
}
