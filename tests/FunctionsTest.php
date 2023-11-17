<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\Just;
use thgs\Functional\Data\Maybe;

use thgs\Functional\Data\Nothing;
use thgs\Functional\Typeclass\Attribute\FunctorInstance;
use function thgs\Functional\fmap;

class FunctionsTest extends TestCase
{
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
    }


    public function testShowWillTypeErrorWhenCannotShow(): void
    {
        $notShow = new class () { public int $a = 5; };
        $data = new Maybe(new Just($notShow));

        $this->expectException(TypeError::class);

        (string) $data;
    }

    public function testCanFmapAMarkedFunctor(): void
    {
        $value = new Just(2);
        $typeThatIsAFunctor = new #[FunctorInstance(fmap: 'myMap')] class($value)
        {
            public function __construct(private readonly Nothing|Just $x)
            {
            }

            public function getValue(): Nothing|Just
            {
                return $this->x;
            }

            public function myMap(callable $f)
            {
                return new static(match (true) {
                    $this->x instanceof Nothing     => new Nothing(),
                    // todo: use Composition ?
                    $this->x instanceof Just        => new Just( $f ( $this->x->getValue() ) ),
                });
            }
        };

        // (a -> b) in this case (Int -> Bool)
        $function = function (int $x): bool { return $x == 2; };

        // therfore the fmap is
        // fmap :: (Int -> Bool) -> f Int -> f Bool

        $mapped = fmap($function, $typeThatIsAFunctor);     // :: f b
        $value = $mapped->getValue();                       // :: b

        $this->assertInstanceOf(Just::class, $value, 'not an instance of Just');
        $this->assertTrue($value->getValue());
    }
}