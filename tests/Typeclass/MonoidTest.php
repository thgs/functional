<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Container\TypeName;
use thgs\Functional\Data\Monoid\Sum;
use thgs\Functional\Typeclass\Monoid;
use function thgs\Functional\mappend;

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

    public function testCanReturnRegisteredMempty(): void
    {
        Monoid::register(
            typePredicate: is_int(...),
            mempty: 1,
            mappend: fn (int $a, int $b): int => $a * $b,
            instanceName: 'Product' // tricky to find a name here? using Product/Sum for now.
        );

        $this->assertEquals(1, Monoid::mempty('Product'));
    }

    public function testWillThrowOnUnknownTypeForMempty(): void
    {
        $this->expectException(\TypeError::class);
        // todo: fix state of containers between tests
        Monoid::mempty('Something unknown');
    }

    public function testCanMappendWithMonoidInstance(): void
    {
        $sum1 = new Sum(123);
        $sum2 = new Sum(123);

        $mappendResult = mappend($sum1, $sum2);

        $this->assertEquals(123+123, $mappendResult->getValue());
    }

    public function testWillThrowOnUnknownTypeForMappend(): void
    {
        $this->expectException(\TypeError::class);
        $mappendResult = mappend(1, 2, new TypeName('unknown'));
    }
}
