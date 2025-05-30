<?php

use PHPUnit\Framework\TestCase;
use function thgs\Functional\lr;
use function thgs\Functional\rl;

class FunctionCompositionTest extends TestCase
{
    public function testCanCompose2(): void
    {
        $composed = lr(
            fn (int $x): bool => $x == 16,
            fn (int $x): int  => (int) ($x / 2),
        );

        $this->assertTrue($composed(32));
    }

    public function testCanCompose5(): void
    {
        $composed = lr(
            fn (int $x): bool => $x == 16,
            fn (int $x): int  => (int) ($x / 2),
            fn (int $x): int => pow($x, 5),
            fn (array $items): int => count($items),
            array_filter(...)
        );

        $this->assertTrue($composed(["one", "two", ""]));
    }

    public function testCanReverseCompose2(): void
    {
        $composed = rl(
            fn (int $x): int  => (int) ($x / 2),
            fn (int $x): bool => $x == 16,
        );

        $this->assertTrue($composed(32));
    }

    public function testCanReverseCompose5(): void
    {
        $composed = rl(
            array_filter(...),
            fn (array $items): int => count($items),
            fn (int $x): int => pow($x, 5),
            fn (int $x): int  => (int) ($x / 2),
            fn (int $x): bool => $x == 16,
        );

        $this->assertTrue($composed(["one", "two", ""]));
    }
}
