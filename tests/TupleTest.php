<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\Tuple;

use thgs\Functional\Testing\EqAssertions;
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

    public function testCanUnwrap(): void
    {
        // todo: is there a better implementation instead of ArrayAccess here?
        $tuple = Tuple::new(123, "hello world");
        [$fst, $snd] = $tuple;

        $this->assertEquals(123, $fst);
        $this->assertEquals("hello world", $snd);
    }

    public function testCanGetNewTuple(): void
    {
        // todo: no remove this. just make new tuples.
        $tuple = Tuple::new(123, "hello world");
        $tuple[0] = 'one';
        $tuple[1] = 'two';
        
        $this->assertTupleIs('one', 'two', $tuple);
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
