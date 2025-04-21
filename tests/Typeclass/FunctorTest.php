<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Typeclass\Functor;
use function thgs\Functional\fmap;
use function thgs\Functional\partial;

class FunctorTest extends TestCase
{
    public function testCanFmapWithRegisteredInstance(): void
    {
        $fmapImplementation =
            /**
             * @param \Closure(int):int $f
             */
            function (\Closure $f, Wrap $w): Wrap {
                return new Wrap($f($w->n));
            };

        Functor::register(
            /**
             * @phpstan-assert-if-true Wrap $x
             */
            typePredicate: fn ($x) => $x instanceof Wrap,

            fmap: fn (\Closure $f, Wrap $w): Wrap => new Wrap(partial($f, $w->n)),
            instanceName: 'Wrap'
        );

        $result = fmap(
            fn (int $a): int => $a * 3,
            new Wrap(4)
        );

        $this->assertInstanceOf(Wrap::class, $result);
        $this->assertEquals(12, $result->n);
    }
}

readonly class Wrap
{
    public function __construct(public int $n)
    {
    }
}
