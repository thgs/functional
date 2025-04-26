<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Container\TypeName;
use thgs\Functional\Typeclass\Monoid;

class MonoidTest extends TestCase
{
    public function testCanReturnRegisteredMappend(): void
    {
        Monoid::register(
            typePredicate: is_int(...),
            mempty: 1,
            mappend: fn (int $a, int $b): int => $a * $b,
            instanceName: 'integer'
        );

        $result = Monoid::mappend(3, 4);
        $this->assertEquals(12, $result);
    }

    public function testCanDifferentiateWhenGivenType(): void
    {
        Monoid::register(
            typePredicate: is_int(...),
            mempty: 1,
            mappend: fn (int $a, int $b): int => $a * $b,
            instanceName: 'Product' // tricky to find a name here? using Product/Sum for now.
        );

        Monoid::register(
            typePredicate: is_int(...),
            mempty: 0,
            mappend: fn (int $a, int $b): int => $a + $b,
            instanceName: 'Sum'
        );

        // this should use "Product" instance as will match first
        $result = Monoid::mappend(3, 4);
        $this->assertEquals(12, $result);

        // this should use "Sum" instance as we explicitly pass it
        $result = Monoid::mappend(3, 4, new TypeName('Sum'));
        $this->assertEquals(7, $result);
    }
}
