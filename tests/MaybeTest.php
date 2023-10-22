<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\Just;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Typeclass\EqInstance;

class MaybeTest extends TestCase
{
    public function testFmap()
    {
        $data = new Maybe(new Just(3));
        $mapped = $data->fmap(fn (int $x) => $x + 2);

        $value = $mapped->getValue();
        $this->assertInstanceOf(Maybe::class, $mapped, 'result not an instance of Maybe');
        $this->assertInstanceOf(Just::class, $value, 'value not an instance of Just');
        $this->assertEquals(5, $value->getValue());
    }


    // todo: add tests for functor laws. could be generic/parameterized?
    public function testIsAFunctor()
    {
        // 1. If we map the `id` function over a functor, the functor that we get back should be the same as the original
        $data = new Maybe(new Just(3));
        $id = fn ($x) => $x;
        $result = $data->fmap($id);

        // for catching errors we do steps
        $this->assertInstanceOf(Maybe::class, $result, 'result not an instance of Maybe');
        $this->assertInstanceOf(Just::class, $result->getValue(), 'value not an instance of Just');
        $this->assertEquals(3, $result->getValue()->getValue());
        $this->assertEquals($data, $result, 'result not same');

        // 2. fmap (f . g) F = fmap f (fmap g F)

        // Int -> Bool
        $f = fn (int $x): bool => $x == 5;
        // Bool -> string
        $g = fn (bool $x): string => $x == true ? '100' : '500';
        // Int -> string
        $c = fn ($l) => $f($g($l));

        $data = new Maybe(new Just(5));
        $result1 = $data->fmap($c);

        $result2 = $data
            ->fmap($g)
            ->fmap($f);

        // todo: is this enough?
        $this->assertEquals($result1, $result2);
    }

    public function testCanEq(): void
    {
        $data = new Maybe(new Just(67));
        $other = new Maybe(new Just(67));

        $this->assertTrue($data->equals($other));
    }

    public function testCanShow(): void
    {
        $data = new Maybe(new Just(67));
        $this->assertEquals('Just 67', (string) $data);
    }

    public function testEnforcesMaybeTypeConstraintOnEq(): void
    {
        // In PHP we have to go "in reverse", adding a constraint in the instances of Eq

        $data = new Maybe(new Just(67));
        $fiction = new class implements EqInstance {
            public function getValue()
            {
                return new Just(67);
            }

            public function equals(EqInstance $other): bool
            {
                return false;
            }

            public function notEquals(EqInstance $other): bool
            {
                return false;
            }
        };

        $this->expectException(TypeError::class);
        $data->equals($fiction);
    }
}