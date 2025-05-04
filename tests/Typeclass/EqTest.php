<?php

use PHPUnit\Exception;
use PHPUnit\Framework\TestCase;
use thgs\Functional\Typeclass\Eq;
use function thgs\Functional\equals;
use function thgs\Functional\just;
use function thgs\Functional\notEquals;

class EqTest extends TestCase
{
    public function testCanDefaultToPhpEquals(): void
    {
        $result = equals(123, 123);
        $this->assertTrue($result);
    }

    public function testCanDefaultToPhpNotEquals(): void
    {
        $result = notEquals(12, 123);
        $this->assertTrue($result);
    }

    public function testCanEqualsWithEqInstance(): void
    {
        $a = just(110);
        $b = just(110);

        $result = equals($a, $b);
        $this->assertTrue($result);
    }

    public function testCanNotEqualsWithEqInstance(): void
    {
        $a = just(110);
        $b = just(111);

        $result = notEquals($a, $b);
        $this->assertTrue($result);
    }

    public function testCanEqualsAndDeriveNotEqualsWithRegisteredInstance(): void
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

    public function testCanEqualsAndDeriveFromNotEqualsWithRegisteredInstance(): void
    {
        $notEqualsImplementation = fn (int|float $a, int|float $b): bool  => ((int) $a) != ((int) $b);

        Eq::register(
            instanceName: 'int|float',
            typePredicate: fn ($x) => is_int($x) || is_float($x),
            equals: null,
            notEquals: $notEqualsImplementation
        );

        $result = equals(12.6, 12.1);
        $this->assertTrue($result);

        $result = notEquals(12.6, 13.1);
        $this->assertTrue($result);
    }

    public function testWillThrowWhenCannotDerive(): void
    {
        $this->expectException(\Exception::class);
        
        Eq::register(
            instanceName: 'int|float',
            typePredicate: fn ($x) => is_int($x) || is_float($x),
            equals: null,
            notEquals: null,
        );
    }
}
