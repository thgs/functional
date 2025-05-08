<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\Just;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Testing\FunctorLawsAssertions;

use function thgs\Functional\c;
use function thgs\Functional\const_;
use function thgs\Functional\eq1;
use function thgs\Functional\flip;
use function thgs\Functional\fmap;
use function thgs\Functional\memoize;
use function thgs\Functional\unit;

class FunctionsTest extends TestCase
{
    use FunctorLawsAssertions;

    public function testFmap(): void
    {
        $data = new Maybe(new Just(5));

        // this fmap is :: (Int -> Bool) -> Maybe Int -> Maybe Bool
        $mapped = fmap(
            fn (int $x): Bool => $x == 5,
            $data
        );

        $value = $mapped->getValue();

        $this->assertInstanceOf(Maybe::class, $mapped, 'result not an instance of Maybe');
        $this->assertInstanceOf(Just::class, $value, 'value not an instance of Just');
        $this->assertEquals(true, $value->getValue());

        $this->assertInstanceIsFunctor(
            $mapped,
            fn (bool $x) => !$x,
            fn (bool $x) => $x && true
        );
    }

    public function testFmapWithComposition(): void
    {
        $data = new Maybe(new Just(5));

        // this fmap is :: (Int -> Bool) -> Maybe Int -> Maybe Bool
        $mapped = fmap(
            c (fn (int $x): Bool => $x == 5),
            $data
        );

        $value = $mapped->getValue();

        $this->assertInstanceOf(Maybe::class, $mapped, 'result not an instance of Maybe');
        $this->assertInstanceOf(Just::class, $value, 'value not an instance of Just');
        $this->assertEquals(true, $value->getValue());

        $this->assertInstanceIsFunctor(
            $mapped,
            fn (bool $x) => !$x,
            fn (bool $x) => $x && true
        );
    }

    public function testShowWillTypeErrorWhenCannotShow(): void
    {
        $notShow = new class () { public int $a = 5; };
        $data = new Maybe(new Just($notShow));

        $this->expectException(TypeError::class);

        (string) $data;
    }

    /**
     * This is just a preliminary test with the very basic functionality.
     */
    public function testMemoizeCanMemoize(): void
    {
        $callsCounter = [];
        $memoized = memoize(function (int $x) use (&$callsCounter) : int {
            $callsCounter[$x] = isset($callsCounter[$x])
                ? $callsCounter[$x] + 1
                : 1;
            
            return $x * $x;
        });
        
        foreach (range(0,4) as $i) {
            $memoized($i);
        }

        foreach (range(0,4) as $i) {
            $memoized($i);
        }

        $this->assertEquals([1,1,1,1,1], $callsCounter);
    }

    /**
     * This is just to check that the memoization storage is separate
     * for each call to create a memoized function (call to memoize()).
     * Hopefully the test is correct.
     */
    public function testMemoizeCanMemoizeTwoFunctions(): void
    {
        $callsCounter1 = [];
        $memoized1 = memoize(function (int $x) use (&$callsCounter1) : int {
            $callsCounter1[$x] = isset($callsCounter1[$x])
                ? $callsCounter1[$x] + 1
                : 1;
            
            return $x * $x;
        });
        
        $callsCounter2 = [];
        $memoized2 = memoize(function (int $x) use (&$callsCounter2) : int {
            $callsCounter2[$x] = isset($callsCounter2[$x])
                ? $callsCounter2[$x] + 1
                : 1;
            
            return $x * $x;
        });


        foreach (range(0,4) as $i) {
            $memoized1($i);
        }

        foreach (range(0,4) as $i) {
            $memoized1($i);
        }

        $this->assertEquals([1,1,1,1,1], $callsCounter1);

        foreach (range(0,4) as $i) {
            $memoized2($i);
        }

        foreach (range(0,4) as $i) {
            $memoized2($i);
        }

        $this->assertEquals([1,1,1,1,1], $callsCounter2);
    }

    public function testConst(): void
    {
        $this->assertEquals(3, const_(3,4));
    }

    public function testUnit(): void
    {
        $this->assertNull(unit());
    }

    public function testFlip(): void
    {
        $f = fn ($x, $y) => $x - $y;
        $result = flip ($f) (2, 10);
        $this->assertEquals(8, $result);
    }

    public function testEq1(): void
    {
        $a = new \stdClass;
        $a->value = 1;

        $b = new \stdClass;
        $b->value = '1';

        $this->assertTrue(eq1($a, $b));
    }
}
