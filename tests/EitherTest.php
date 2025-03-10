<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\Either;
use thgs\Functional\Data\Left;
use thgs\Functional\Data\Right;
use thgs\Functional\Testing\EqAssertions;

class EitherTest extends TestCase
{
    use EqAssertions;

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

    public function testImplementsEqCorrectly(): void
    {
        $this->assertImplementsEqCorrectly(new Either(new Right(123)), new Either(new Right(456)));
        $this->assertImplementsEqCorrectly(new Either(new Left(123)), new Either(new Right(456)));
    }
}
