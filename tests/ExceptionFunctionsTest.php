<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\Just;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Data\Nothing;
use function thgs\Functional\safe;

class ExceptionFunctionsTest extends TestCase
{
    public function testCanSilentExceptions(): void
    {
        $return = safe(function () {
            throw new \Exception('Hellooo!');
        });

        $this->assertInstanceOf(Maybe::class, $return);
        $this->assertInstanceOf(Nothing::class, $return->getValue());
    }

    public function testCanReturnValue(): void
    {
        $return = safe(fn (): int => 123);

        $this->assertInstanceOf(Maybe::class, $return);
        $this->assertInstanceOf(Just::class, $return->getValue());
        $this->assertEquals(123, $return->getValue()->getValue());
    }

    public function testCanAcceptAndPassParameters(): void
    {
        $return = safe(fn ($x): int => 123 + $x, 100);

        $this->assertInstanceOf(Maybe::class, $return);
        $this->assertInstanceOf(Just::class, $return->getValue());
        $this->assertEquals(223, $return->getValue()->getValue());
    }
}
