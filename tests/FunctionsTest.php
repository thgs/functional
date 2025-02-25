<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\Just;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Testing\FunctorLawsAssertions;

use function thgs\Functional\fmap;

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

    public function testShowWillTypeErrorWhenCannotShow(): void
    {
        $notShow = new class () { public int $a = 5; };
        $data = new Maybe(new Just($notShow));

        $this->expectException(TypeError::class);

        (string) $data;
    }
}
