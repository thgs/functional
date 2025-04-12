<?php

use PHPUnit\Framework\TestCase;
use function thgs\Functional\assertCompositionRespectsIdentity;
use function thgs\Functional\functionComposition;

class AssertionsTest extends TestCase
{
    public function testCanAssertFunctionCompositionRespectsIdentity(): void
    {
        $this->assertTrue(
            assertCompositionRespectsIdentity(
                functionComposition(...),
                fn ($x) => $x * 2,
                7));
    }

    public function testCanAssertFunctionCompositionDoesNotRespectsIdentity(): void
    {
        $this->assertFalse(
            assertCompositionRespectsIdentity(
                fn ($left, $right) => $left,
                fn ($x) => $x * 2,
                7));
    }
}
