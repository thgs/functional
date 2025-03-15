<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Wrapper\Wrapper;
use thgs\Functional\Data\Tuple;
use thgs\Functional\Data\Tuple3;

use function thgs\Functional\t;
use function thgs\Functional\t3;

class WrapperTest extends TestCase
{
    public function testCanWrapWithInputOfTwo(): void
    {
        $wrapper = Wrapper::withAdjustedInput(fn (int $x, int $y): int => $x + $y);
        $result  = $wrapper( t(2, 3) );
        $this->assertEquals(5, $result);
    }

    public function testCanWrapWithInputOfThree(): void
    {
        $wrapper = Wrapper::withAdjustedInput(fn (int $x, int $y, int $z): int => ($x + $y) * $z);
        $result  = $wrapper( t3(2, 3, 2) );
        $this->assertEquals(10, $result);
    }

    public function testCanWrapClosureWithNoArguments(): void
    {
        $wrapper = Wrapper::withAdjustedInput(fn (): int => 5);
        $this->assertEquals(5, $wrapper());
    }

    public function testCanWrapClosureWithSingleArgument(): void
    {
        $wrapper = Wrapper::withAdjustedInput(fn ($x): int => $x + 5);
        $this->assertEquals(7, $wrapper(2));
    }

    public function testCanWrapWithGivenInputAdjustment(): void
    {
        $wrapper = Wrapper::withAdjustedInput(
            fn ($x, $y) => $x - $y,
            fn (Tuple $p): Tuple => $p->swap());

        $result = $wrapper (t (3, 10));
        $this->assertEquals(7, $result);
    }

    public function testCanAdjustInputTwiceWithAdjustedInputOfTuple2(): void
    {
        $wrapper    = Wrapper::withAdjustedInput(fn (Tuple $p) => $p->fst() - $p->snd());
        $newWrapper = $wrapper->contramap(fn (Tuple $p) => t($p->snd(), $p->fst()));
        $result     = $newWrapper( t(3,10) );
        $this->assertEquals(7, $result);
    }


    public function testCanAdjustOutput(): void
    {
        $wrapper    = Wrapper::withAdjustedInput(fn (int $x, int $y): int => $x + $y);
        $newWrapper = $wrapper->fmap(fn ($result) => $result * 2);
        $result     = $newWrapper( t(2, 3) );
        $this->assertEquals(10, $result);
    }

    public function testCanWrapWithAdjustedInputOfTuple2(): void
    {
        $wrapper = Wrapper::withAdjustedInput(fn (Tuple $p) => $p->fst() + $p->snd());
        $result  = $wrapper( t(2,3) );
        $this->assertEquals(5, $result);
    }

    public function testCanWrapWithAdjustedInputOfTuple2ReturningTuple(): void
    {
        $wrapper = Wrapper::withAdjustedInput(fn (Tuple $p): Tuple => $p->swap());
        $result  = $wrapper( t(3,4) );
        $this->assertEquals(t(4,3), $result);
    }

    public function testCanAdjustOutputWithAdjustedInputOfTuple2(): void
    {
        $wrapper    = Wrapper::withAdjustedInput(fn (Tuple $p) => $p->fst() + $p->snd());
        $newWrapper = $wrapper->fmap(fn (int $result) => "Result is $result");
        $result     = $newWrapper( t(2,3) );
        $this->assertEquals("Result is 5", $result);
    }


    public function testCanAdjustOutputGivenInputOfThree(): void
    {
        $closure    = fn (int $x, int $y, int $z): int => ($x + $y) * $z;
        $wrapper    = Wrapper::withAdjustedInput($closure);
        $newWrapper = $wrapper->fmap(fn (int $result):string => "The result is $result\n");
        $result     = $newWrapper(t3 (2, 5, 2)); // (2+5) * 2 = 14
        $this->assertEquals("The result is 14\n", $result);
    }

    public function testWillThrowWhenExpandingInput(): void
    {
        $initial = static fn (int $x, int $y): int => $x * $y;
        $wrapper = Wrapper::withAdjustedInput($initial);
        $result  = $wrapper(t(3,4));

        $this->assertEquals(12, $result);
        $this->expectException(TypeError::class);
        $newWrapper = $wrapper->adjustInput(fn (Tuple $p, int $z): Tuple => t($p->fst() * $z, $p->snd() * $z));
    }
}
