<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\NonEmptyArray;

class NonEmptyArrayTest extends TestCase
{
    public function testWillTypeErrorWhenGivenEmptyArray(): void
    {
        $this->expectException(\TypeError::class);
        new NonEmptyArray([]); // phpstan complains here
    }

    public function testCanIterate(): void
    {
        $a = new NonEmptyArray([1,2,3]);
        $returns = [];
        foreach ($a as $i) {
            $returns[] = $i;
        }
        $this->assertEquals([1,2,3], $returns);
    }

    /**
     * Checking the output types
     */
    public function testCanReturnNonEmptyArray(): void
    {
        $a = new NonEmptyArray([1,2,3]);
        $returns = $a->toArray();

        /**
         * This should pass
         * @phpstan-assert non-empty-array<int> $returns
         * @phpstan-assert int $returns[0]
         */

        $this->assertEquals([1,2,3], $returns);
    }
}
