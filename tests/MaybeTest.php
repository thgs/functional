<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Control\IO;
use thgs\Functional\Control\Typeclass\ApplicativeInstance;
use thgs\Functional\Control\Typeclass\MonadInstance;
use thgs\Functional\Data\Just;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Data\Nothing;
use thgs\Functional\Testing\EqAssertions;
use thgs\Functional\Testing\FunctorLawsAssertions;
use thgs\Functional\Typeclass\EqInstance;

use function thgs\Functional\fmap;
use function thgs\Functional\show;

class MaybeTest extends TestCase
{
    use FunctorLawsAssertions;
    use EqAssertions;

    public function testFmap(): void
    {
        $data = new Maybe(new Just(3));
        $mapped = fmap(fn (int $x) => $x + 2, $data);

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

    public function testImplementsEqCorrectly(): void
    {
        $this->assertImplementsEqCorrectly(Maybe::pure(3), Maybe::pure(2));
        $this->assertImplementsEqCorrectly(Maybe::pure(3), new Maybe(new Nothing()));
    }

    public function testCanShow(): void
    {
        $data = new Maybe(new Just(67));
        $this->assertEquals('Just 67', show($data));
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

    /**
     * (+) <*> Just 3 <*> Just 4   = Just 7
     */
    public function testCanSequenceAndPartiallyApply(): void
    {
        $result =
            Maybe::pure(fn ($x, $y) => $x + $y)
            ->sequence(Maybe::pure(3))
            ->sequence(Maybe::pure(4));

        $this->assertEquals(7, $result->getValue()->getValue());
    }

    /**
     * Not so sure if this should be enforced yet.
     */
    public function testThrowsWhenSequenceWithAnotherApplicativeInstance(): void
    {
        $this->expectException(TypeError::class);

        Maybe::pure(fn ($x, $y) => $x + $y)
            ->sequence(IO::pure(fn () => 3));
    }

    /**
     * (+) <$> Just 3 <*> Just 5 == Just 8
     */
    public function testCanFmapAndThenSequence(): void
    {
        $result =
            fmap(
                fn ($x, $y) => $x + $y,
                Maybe::pure(3)
            )
            ->sequence(Maybe::pure(5));

        $this->assertEquals(8, $result->getValue()->getValue());
    }

    public function testCanBind(): void
    {
        $maybe = Maybe::inject(123);

        $result = $maybe->bind(fn (int $a) => Maybe::inject($a * 2));

        $this->assertInstanceOf(MonadInstance::class, $result);

        $just = $result->getValue();
        $this->assertInstanceOf(Just::class, $just);
        $this->assertEquals(123 * 2, $just->getValue());
    }

    // todo: test Applicative sequencing (<*>) with more than 2

    // todo: test what happens when the callable passed to bind does not return MonadInstance
}
