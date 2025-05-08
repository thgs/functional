<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\Either;
use thgs\Functional\Data\Just;
use thgs\Functional\Data\Left;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Data\Nothing;
use thgs\Functional\Data\Right;
use function thgs\Functional\just;
use function thgs\Functional\left;
use function thgs\Functional\nothing;
use function thgs\Functional\right;

class ConstructorsTest extends TestCase
{
    public function testCanConstructJust(): void
    {
        $result = just(123);
        $this->assertInstanceOf(Maybe::class, $result);
        $this->assertInstanceOf(Just::class, $result->getValue());
        $this->assertEquals(123, $result->getValue()->getValue());
    }

    public function testCanConstructNothing(): void
    {
        $result = nothing();
        $this->assertInstanceOf(Maybe::class, $result);
        $this->assertInstanceOf(Nothing::class, $result->getValue());
        $this->assertEquals(null, $result->getValue()->getValue());
    }

    public function testCanConstructRight(): void
    {
        $result = right(5);
        $this->assertInstanceOf(Either::class, $result);
        $this->assertInstanceOf(Right::class, $result->getValue());
        $this->assertEquals(5, $result->getValue()->getValue());
    }

    public function testCanConstructLeft(): void
    {
        $result = left(5);
        $this->assertInstanceOf(Either::class, $result);
        $this->assertInstanceOf(Left::class, $result->getValue());
        $this->assertEquals(5, $result->getValue()->getValue());
    }
}
