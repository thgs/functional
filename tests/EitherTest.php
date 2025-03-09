<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\Either;
use thgs\Functional\Data\Left;
use thgs\Functional\Data\Right;

class EitherTest extends TestCase
{
    public function testCanReturnLeftValue(): void
    {
        $either = new Either(new Left(123));
        $this->assertEquals(123, $either->getValue()->getValue());
    }

    public function testCanReturnRightValue(): void
    {
        $either = new Either(new Right(123));
        $this->assertEquals(123, $either->getValue()->getValue());
    }

    // todo: these could be part of a trait ?
    // with the runtime checks behaviour as well?
    // apart from the laws there is some boilerplate.
    public function testCanEq(): void
    {
        $either = new Either(new Right(123));
        $either2 = new Either(new Right(123));

        $this->assertTrue($either->equals($either2));

        $either = new Either(new Right(123));
        $either2 = new Either(new Left(123));

        $this->assertTrue($either->notEquals($either2));
    }
}
