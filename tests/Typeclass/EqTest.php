<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Typeclass\Eq;
use function thgs\Functional\equals;
use function thgs\Functional\notEquals;

class EqTest extends TestCase
{
    public function testCanEqualsAndDeriveWithRegisteredInstance(): void
    {
        $equalsImplementation = fn (int|float $a, int|float $b): bool  => ((int) $a) == ((int) $b);

        Eq::register(
            instanceName: 'int|float',
            typePredicate: fn ($x) => is_int($x) || is_float($x),
            equals: $equalsImplementation,
            notEquals: null
        );

        $result = equals(12.6, 12.1);
        $this->assertTrue($result);

        $result = notEquals(12.6, 13.1);
        $this->assertTrue($result);
    }
}
