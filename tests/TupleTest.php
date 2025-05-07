<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\Tuple;

use thgs\Functional\Testing\EqAssertions;
use thgs\Functional\Typeclass\EqInstance;
use function thgs\Functional\partial;
use function thgs\Functional\t;

class TupleTest extends TestCase
{
    use EqAssertions;

    public function testCanCreateTuple(): void
    {
        $tuple = Tuple::new(123, "hello world");
        $this->assertTupleIs(123, "hello world", $tuple);
    }

    public function testCanSwapTupleValues(): void
    {
        $tuple = Tuple::new(123, "hello world")->swap();
        $this->assertTupleIs("hello world", 123, $tuple);
    }

    public function testCanCreateADupe(): void
    {
        $dupe = Tuple::dupe(123);
        $this->assertTupleIs(123, 123, $dupe);
    }

    public function testCanApplyFunctionToBoth(): void
    {
        $dupe = Tuple::dupe(125);
        $both = Tuple::both(fn ($x) => $x * 2, $dupe);
        $this->assertTupleIs(250, 250, $both);
    }

    public function testCanCurry(): void
    {
        $result = Tuple::curry(
            fn (Tuple $t) => [(string) $t->fst() => strtoupper($t->snd())],
            12,
            "twelve");

        $this->assertIsArray($result);
        $this->assertArrayHasKey('12', $result);
        $this->assertEquals('TWELVE', $result['12']);
    }

    public function testCanCurryWithPartial(): void
    {
        $partialCurry = partial (Tuple::curry(...), fn (Tuple $t) => [(string) $t->fst() => strtoupper($t->snd())]);
        $result = $partialCurry(12, "twelve");
        // todo: the above works but we cannot give the arguments one by one, so partial seems to have an issue.
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('12', $result);
        $this->assertEquals('TWELVE', $result['12']);
    }

    public function testCanUncurry(): void
    {
        $f = fn ($a, $b) => $a * $b;

        // todo: is this implemented only as partial/curried ?
        $partialUncurry = Tuple::uncurry($f);

        // Now calling it by passing a tuple
        $result = $partialUncurry(t(10, 4));

        $this->assertEquals(40, $result);
    }

    public function testImplementsEq(): void
    {
        $this->assertImplementsEqCorrectly(Tuple::new(1,3), Tuple::new(3,8));
    }

    public function testCanBimap(): void
    {
        $t = t(123, 456);
        $result = $t->bimap(fn ($x) => $x + 1, fn ($x) => $x - 1);
        $this->assertTupleIs(124, 455, $result);
    }

    public function testWillReturnFalseWhenEqInstanceEqualsWithAnotherType(): void
    {
        $t = t(123, 456);
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
        $this->assertFalse($t->equals($anotherInstance));
    }

    /**
     * @template A
     * @template B
     * @param A $expectedA
     * @param B $expectedB
     * @param Tuple<A,B> $tuple
     */
    private function assertTupleIs(mixed $expectedA, mixed $expectedB, Tuple $tuple): void
    {
        $this->assertEquals($expectedA, $tuple->fst());
        $this->assertEquals($expectedB, $tuple->snd());
    }
}
