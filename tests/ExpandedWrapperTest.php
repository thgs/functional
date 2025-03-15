<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Wrapper\ExpandedWrapper as Wrapper;
use thgs\Functional\Data\Tuple;

use function thgs\Functional\t;

class ExpandedWrapperTest extends TestCase
{
    public function testCanWrapClosure(): void
    {
        $wrapper = new Wrapper(fn (int $x) => $x+1);
        $result = $wrapper(123);
        $this->assertEquals(124, $result);
    }

    public function testCanWrapWithInputOfTwo(): void
    {
        // todo: this has an issue with the types because we type hint
        // as callable(A) and A is a single parameter.
        $wrapper = new Wrapper(fn (int $x, int $y): int => $x + $y);
        $result = $wrapper( 2, 3 );
        $this->assertEquals(5, $result);
    }

    public function testCanAutomaticallyAdjustInput(): void
    {
        $initial = static fn ($x, $y) => $x * $y;
        $wrapper = Wrapper::withAdjustedInput($initial);

        $result = $wrapper (t(3,4));
        $this->assertEquals(12, $result);
    }

    public function testCanAdjustOutputWithInputOfTwo(): void
    {
        $wrapper = new Wrapper(fn (int $x, int $y): int => $x + $y);
        $newWrapper = $wrapper->fmap(fn ($result) => $result * 2);
        $result = $newWrapper( 2, 3 );
        $this->assertEquals(10, $result);
    }

    public function testCanAdjustInputWithGivenInputOfTwo(): void
    {
        /**
         * @todo fix this case, this might be hard because we do not
         * know if this is supposed to return arguments in array or
         * one argument that is an array.
         */
        $this->markTestSkipped();
        $wrapper = new Wrapper(fn (int $x, int $y): float => $x / $y);
        $newWrapper = $wrapper->contramap(fn (Tuple $p): array => [$p->snd(), $p->fst()]); 

        $result = $newWrapper( t(10, 2) );
        $this->assertEquals(2/10, $result(2));
    }

    public function testCanWrapWithAdjustedInputOfTuple2(): void
    {
        $wrapper = Wrapper::withAdjustedInput(fn (Tuple $p) => $p->fst() + $p->snd());
        $result = $wrapper( t(2,3) );
        $this->assertEquals(5, $result);
    }

    public function testCanWrapWithAdjustedInputOfTuple2ReturningTuple(): void
    {
        $wrapper = Wrapper::withAdjustedInput(fn (Tuple $p): Tuple => t($p->fst() , $p->snd()));
        $result = $wrapper( t(3,3) );
        $this->assertEquals(t(3,3), $result);
    }

    public function testCanAdjustOutputWithAdjustedInputOfTuple2(): void
    {
        $wrapper = Wrapper::withAdjustedInput(fn (Tuple $p) => $p->fst() + $p->snd());
        $newWrapper = $wrapper->fmap(fn (int $result) => "Result is $result");
        $result = $newWrapper( t(2,3) );
        $this->assertEquals("Result is 5", $result);
    }

    public function testCanAdjustInputTwiceWithAdjustedInputOfTuple2(): void
    {
        $wrapper = Wrapper::withAdjustedInput(fn (Tuple $p) => $p->fst() - $p->snd());
        $newWrapper = $wrapper->contramap(fn (Tuple $p) => t($p->snd(), $p->fst()));
        $result = $newWrapper( t(3,10) );
        $this->assertEquals(7, $result);
    }

    public function testCanAdjustOutputGivenInputOfThree(): void
    {
        $exampleClosure = fn (int $x, int $y, int $z): int => ($x + $y) * $z;
        $wrapper = new Wrapper($exampleClosure); // no input adjustment
        $newWrapper = $wrapper->fmap(fn (int $result):string => "The result is $result\n");
        $result = $newWrapper(2, 5, 2); // (2+5) * 2 = 14
        $this->assertEquals("The result is 14\n", $result);
    }

    public function testClosureCanDoVariadic(): void
    {
        $fn = fn (...$xs) => count($xs);
        $result = $fn(1,2,3,4);
        $this->assertEquals(4, $result);

        $f = fn($x, $y) => $x + $y;
        $fn = fn (...$xs) => $f(...$xs);
        $result = $fn(2,3);
        $this->assertEquals(5, $result);
    }

    /**
     * @todo is this even right? Only works because contramap is doing variadic
     * what about the types?
     */
    public function testCanAdjustAndExpandInput(): void
    {
        $initial = static fn (int $x, int $y): int => $x * $y;
        $wrapper = Wrapper::withAdjustedInput($initial);

        $result = $wrapper(t(3,4));
        $this->assertEquals(12, $result);

        $newWrapper = $wrapper->adjustInput(fn (Tuple $p, int $z): Tuple => t($p->fst() * $z, $p->snd() * $z));

        $result = ($newWrapper) (t(3,4), 2); // (3 * 2) * (4 * 2) = 48
        $this->assertEquals(48, $result);
    }

    // todo: add more tests
}
