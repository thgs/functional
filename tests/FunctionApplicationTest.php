<?php

use PHPUnit\Framework\TestCase;
use function thgs\Functional\apply;
use function thgs\Functional\partial;

class FunctionApplicationTest extends TestCase
{
    public function testCanPartiallyApplyInABinaryFunction(): void
    {
        $f = fn (int $x, int $y) => $x + $y;
        $p = partial($f, 23);

        $this->assertInstanceOf(\Closure::class, $p);
        $this->assertEquals(25, $p(2));
    }

    public function testCanFullyApplyABinaryFunction(): void
    {
        $f = fn (int $x, int $y) => $x + $y;
        $p = partial($f, 23, 2);

        $this->assertIsInt($p);
        $this->assertEquals(25, $p);
    }

    public function testCanApplySingleArgument(): void
    {
        $f = fn (int $x) => $x + 8;

        $p = apply($f, 32);

        $this->assertIsInt($p);
        $this->assertEquals(40, $p);
    }
}
