<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Instance\Composition;
use function thgs\Functional\fmap;

class CompositionTest extends TestCase
{
    public function testFmap(): void
    {
        $composition = new Composition(function ($x) { return $x + 100; });

        $result = fmap(function ($x) { return $x * 3; }, $composition);

        $this->assertIsCallable($result);
        $this->assertInstanceOf(Composition::class, $result);

        $fullyApplied = $result(3);

        $this->assertEquals(309, $fullyApplied);
    }

    public function testCanCompose5(): void
    {
        $composition = new Composition(function ($x) { return $x + 100; });
        $result = fmap(function ($x) { return $x * 3; }, $composition);
        $result = fmap(function ($x) { return $x * 2; }, $result);
        $result = fmap(function ($x) { return $x / 2; }, $result);
        $result = fmap(function ($x) { return $x + 1; }, $result);

        $this->assertIsCallable($result);
        $this->assertInstanceOf(Composition::class, $result);

        $fullyApplied = $result(3);

        $this->assertEquals((3 + 100) * 3 * 2 / 2 + 1, $fullyApplied);
    }

    public function testCanLift(): void
    {
        $toLift = function (callable $fa) {
            // fmap (*2)
            // but we have to do it like: Composition fa => fmap (*2) fa
            // because we need to pass a FunctorInstance
            return fmap(fn ($x) => $x * 2, new Composition($fa));
        };
        $composition = new Composition($toLift);

        // we pass (a -> b) and expect (fa -> fb)
        $lifted = $composition(fn ($x) => $x * 2);

        $this->assertIsCallable($lifted);

        $result = $lifted(3);

        $this->assertEquals(12, $result);
    }

    public function testIsAFunctor(): void
    {
        // 1. If we map the `id` function over a functor, the functor that we get back should be the same as the original
        $id = fn ($x) => $x;
        $composition = new Composition(fn ($x) => $x + 2);
        $result = $composition->fmap($id);
        $resultIfCalled = $result(3);

        $this->assertInstanceOf(Composition::class, $result, 'result not an instance of Composition');
        $this->assertEquals($composition, $result, 'result not equal with initial composition');
        $this->assertEquals(5, $resultIfCalled, 'result if called, does not return expected 5');


        // 2. fmap (f . g) F = fmap f (fmap g F)
        $f = fn ($x) => $x * 2;
        $g = fn ($x) => $x + 100;
        $c = fn ($x) => $f($g($x));

        $result1 = new Composition($c);
        $result2 = (new Composition($g))
            ->fmap($f);

        $this->assertEquals($result1(5), $result2(5), 'non associative');
        $this->assertEquals(210, $result1(5), 'failed to compose correctly (expected 210)');
    }
}