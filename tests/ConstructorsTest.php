<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\Just;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Data\Nothing;
use function thgs\Functional\just;
use function thgs\Functional\nothing;

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
}
