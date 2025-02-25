<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\Just;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Data\Nothing;
use thgs\Functional\Proof\FunctorProof;
use thgs\Functional\Typeclass\ApplicativeInstance;
use thgs\Functional\Typeclass\EqInstance;

use function thgs\Functional\fmap;

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

    public function testCanConstructApplicativeWithPure(): void
    {
        $applicative = Maybe::pure(67);

        $this->assertInstanceOf(ApplicativeInstance::class, $applicative);
        $this->assertInstanceOf(Maybe::class, $applicative);
        $this->assertInstanceOf(Just::class, $applicative->getValue());
    }


    public function testCanSequenceApplicatives(): void
    {
        $ap1 = Maybe::pure(fn ($x) => $x + 3);
        $ap2 = Maybe::pure(67);

        $result = $ap1->sequence($ap2);   // Just (+3) <*> Just 67
        // $result :: (Num b) => Maybe b

        $this->assertInstanceOf(ApplicativeInstance::class, $result);
        $this->assertInstanceOf(Maybe::class, $result);

        $unwrapped = $result->getValue();
        $this->assertInstanceOf(Just::class, $unwrapped);
        $this->assertEquals(70, $unwrapped->getValue());
    }

    public function testCanSequenceApplicativesWithNothing(): void
    {
        // Nothing <*> Just 67 :: Nothing
        $apNothing = new Maybe(new Nothing());
        $ap2 = Maybe::pure(fn ($x) => $x + 3);
        $result = $apNothing->sequence($ap2);
        $this->assertInstanceOf(ApplicativeInstance::class, $result);
        $this->assertInstanceOf(Maybe::class, $result);
        $unwrapped = $result->getValue();
        $this->assertInstanceOf(Nothing::class, $unwrapped);

        // Just (+3) <*> Nothing :: Nothing
        $ap1 = Maybe::pure(fn ($x) => $x + 3);
        $apNothing = new Maybe(new Nothing());
        $result = $ap1->sequence($apNothing);
        $this->assertInstanceOf(ApplicativeInstance::class, $result);
        $this->assertInstanceOf(Maybe::class, $result);
        $unwrapped = $result->getValue();
        $this->assertInstanceOf(Nothing::class, $unwrapped);
    }

    public function testCanSequenceAndPartiallyApply(): void
    {
        $this->markTestSkipped(); // skipped for now until we add
                                  // partial application? or what is
                                  // needed here.

        $ap1 = Maybe::pure(fn ($x, $y) => $x + $y);
        $ap2 = new Maybe(new Just(3));
        $ap3 = new Maybe(new Just(4));

        $result = $ap1
            ->sequence($ap2)
            ->sequence($ap3)
            ->getValue();

        $this->assertEquals(7, $result);
    }

    public function testCanFmapAndThenSequence(): void
    {
        $this->markTestSkipped(); // skipped for now until we add
                                  // partial application? or what is
                                  // needed here.

        /** @var Maybe */
        $maybeIntInt = fmap(fn ($x, $y) => $x + $y, Maybe::pure(3));
        $maybeIntInt->sequence(Maybe::pure(5));

        $this->assertEquals(8, $result);
    }
}
