<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Control\IO;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Data\Nothing;
use function thgs\Functional\dn;
use function thgs\Functional\doBind;
use function thgs\Functional\io;
use function thgs\Functional\just;
use function thgs\Functional\nothing;

class DoNotationTest extends TestCase
{
    public function testCanBind(): void
    {
        $result = dn(
            just(4),
            fn (int $x): Maybe => just($x + 5),
        );

        $this->assertInstanceOf(Maybe::class, $result);
        $this->assertEquals(9, $result->getValue()->getValue());
    }

    public function testCanBindMultiple(): void
    {
        $result = dn(
            just(4),
            fn (int $x): Maybe => just($x + 5),
            fn (int $x): Maybe => $x > 10 ? just($x) : nothing()
        );

        $this->assertInstanceOf(Maybe::class, $result);
        $this->assertInstanceOf(Nothing::class, $result->getValue());
    }

    public function testCanBindAndSequence(): void
    {
        $sideEffect = 0;
        
        $result = dn(
            io(function () use (&$sideEffect): int {
                $sideEffect++;
                return 123;
            }),

            function (int $x) use (&$sideEffect): IO {
                $sideEffect = $x; 
                return IO::unit();
            },

            /**
             * @var IO<int>
             */
            io(5)
        );

        $this->assertInstanceOf(IO::class, $result);
        $this->assertEquals(0, $sideEffect);

        $ioResult = $result();

        $this->assertEquals(123, $sideEffect);
        $this->assertEquals(5, $ioResult);
    }

    /**
     * This will change once we support do-notation through the container.
     */
    public function testWillTypeErrorIfBindReturnsNonMonadInstance(): void
    {
        $this->expectException(\TypeError::class);

        dn(
            just(4),
            fn (int $x): int => $x + 5
        );
    }

    public function testDoBindNotationCanBind(): void
    {
        $result = doBind(
            just(4),
            fn (int $x): Maybe => just($x + 5),
            fn (int $x): Maybe => just($x - 3),
        );

        $this->assertInstanceOf(Maybe::class, $result);
        $this->assertEquals(6, $result->getValue()->getValue());
    }
}
