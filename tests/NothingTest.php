<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\Nothing;
use function thgs\Functional\show;

class NothingTest extends TestCase
{
    public function testCanShow(): void
    {
        $nothing = new Nothing();
        $this->assertEquals("Nothing", show($nothing));
    }

    public function testReturnsNullOnGetValue(): void
    {
        $nothing = new Nothing();
        $this->assertNull($nothing->getValue());
    }
}
