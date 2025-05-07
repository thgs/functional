<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\Either;
use thgs\Functional\Data\Left;
use thgs\Functional\Data\Right;
use thgs\Functional\Testing\EqAssertions;
use thgs\Functional\Typeclass\EqInstance;
use function thgs\Functional\equals;

class EitherTest extends TestCase
{
    use EqAssertions;

    public function testCanReturnLeftValue(): void
    {
        $either = new Either(new Left(123));
        $this->assertEquals(123, $either->getValue()->getValue());
    }

    public function testCanReturnRightValue(): void
    {
        $either = new Either(new Right(123));
        $this->assertEquals(123, $either->getValue()->getValue());
    }

    public function testCanTellIfItIsRight(): void
    {
        $either = new Either(new Right(123));
        $this->assertTrue($either->isRight());

        $either = new Either(new Left(123));
        $this->assertFalse($either->isRight());
    }

    public function testImplementsShow(): void
    {
        $either = new Either(new Right(123));
        $this->assertEquals("Right 123", (string) $either);

        $either = new Either(new Left(123));
        $this->assertEquals("Left 123", (string) $either);
    }

    public function testCanBimap(): void
    {
        // todo: add more tests for the Bifunctor laws.

        /** @var Either<int,int> */
        $either = new Either(new Right(123));
        $result = $either->bimap(fn (int $x) => $x + 1, fn (int $x) => $x - 1);

        $this->assertInstanceOf(Right::class, $result->getValue());
        $unwrapped = $result->getValue()->getValue();
        $this->assertEquals(122, $unwrapped);

        /** @var Either<int,int> */
        $either = new Either(new Left(123));
        $result = $either->bimap(fn (int $x) => $x + 1, fn (int $x) => $x - 1);

        $this->assertInstanceOf(Left::class, $result->getValue());
        $unwrapped = $result->getValue()->getValue();
        $this->assertEquals(124, $unwrapped);
    }

    public function testCanFmap(): void
    {
        // todo: add more tests for the Functor laws.

        /** @var Either<int,int> */
        $either = new Either(new Right(123));

        $result = $either->fmap(fn (int $x) => $x + 1);

        $this->assertInstanceOf(Either::class, $result);
        $this->assertInstanceOf(Right::class, $result->getValue());
        $this->assertEquals(124, $result->getValue()->getValue());

        /** @var Either<int,int> */
        $either = new Either(new Left(123));

        $result = $either->fmap(fn (int $x) => $x + 1);

        $this->assertInstanceOf(Either::class, $result);
        $this->assertInstanceOf(Left::class, $result->getValue());
        $this->assertEquals(123, $result->getValue()->getValue());
    }

    /**
     * @todo here some structures return false some throw
     */
    public function testEqualsWillThrowWithAnotherEqInstance(): void
    {
        /** @var Either<int,int> */
        $either = new Either(new Right(123));

        $anotherInstance = new class implements EqInstance {
            /**
             * @param EqInstance<A> $other
             */
            public function equals(EqInstance $other): bool
            {
                return true;
            }

            /**
             * @param EqInstance<A> $other
             */
            public function notEquals(EqInstance $other): bool
            {
                return !$this->equals($other);
            }
        };

        $this->expectException(\TypeError::class);
        equals($either, $anotherInstance);
    }

    public function testImplementsEqCorrectly(): void
    {
        $this->assertImplementsEqCorrectly(new Either(new Right(123)), new Either(new Right(456)));
        $this->assertImplementsEqCorrectly(new Either(new Left(123)), new Either(new Right(456)));
    }
}
