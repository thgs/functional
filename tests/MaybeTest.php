<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\Just;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Proof\FunctorProof;
use thgs\Functional\Typeclass\EqInstance;

class MaybeTest extends TestCase
{
    use FunctorProof;

    public function testFmap()
    {
        $data = new Maybe(new Just(3));
        $mapped = $data->fmap(fn (int $x) => $x + 2);

        $value = $mapped->getValue();
        $this->assertInstanceOf(Maybe::class, $mapped, 'result not an instance of Maybe');
        $this->assertInstanceOf(Just::class, $value, 'value not an instance of Just');
        $this->assertEquals(5, $value->getValue());
    }

    public function testIsAFunctor(): void
    {
        $this->assertInstanceIsFunctor(
            new Maybe(new Just(5)),
            fn (int $x): bool => $x == 5,
            fn (bool $x): string => $x == true ? '100' : '500'
        );
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
