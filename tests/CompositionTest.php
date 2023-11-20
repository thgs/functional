<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Instance\Composition;
use thgs\Functional\Proof\FunctorProof;

use function thgs\Functional\fmap;
use function thgs\Functional\show;

class CompositionTest extends TestCase
{
    use FunctorProof;

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
        $this->assertInstanceIsFunctor(
            new Composition(fn ($x) => $x + 2),
            fn ($x) => $x * 2,
            fn ($x) => $x + 100
        );
    }

    public function testCanComposeWithShow(): void
    {
        $composition = new Composition(fn ($x) => $x * 3);
        $composition = $composition->fmap(show(...));           // show . $g

        $result = $composition(100);

        $this->assertEquals('300', $result);
        $this->assertTrue(is_string($result));
    }

    public function testComposesInReverse(): void
    {
//        $composition = new Composition(fn (int $x): int => $x * 3);
//        $composition->fmap(show(...));                                                   // show . (*3) :: Int => String
//
//        $fmapComp = new Composition(fn ($x) => fmap($x, $composition));       // fmap (show . (*3)) :: f Int -> f String
//
//        // composition now needs 1 parameter (f Int) to return (f String)
//        $fa = new Composition(fn (int $x): int => $x * 100);
//        $result = $fmapComp($fa);                                          // fmap (show . (*3)) (*100) :: Int -> String

        //
        // here to make this work we need to make the composition in reverse (conceptually)
        $fa = fn (int $x): int => $x * 100;
        $fmapComp = new Composition(fn ($x) => fmap($x, new Composition($fa)));
        $result = $fmapComp(fn ($x) => show($x * 3));       // here COULD be a composition too

        $this->assertIsCallable($result);
        $this->assertInstanceOf(Composition::class, $result);

        $calledResult = $result(2);                                         // fmap (show . (*3)) (*100) 2      == "600"

        $this->assertEquals('600', $calledResult, 'result is calculated wrong');
        $this->assertIsString($calledResult, 'show was not applied last');
    }
}