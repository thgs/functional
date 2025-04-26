<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Typeclass\Show;
use function thgs\Functional\show;

class ShowTest extends TestCase
{
    public function testCanShowWithOverridenScalarInstance(): void
    {
        $showImplementation = fn ($x) => "Integer " . $x;
        Show::register(
            typePredicate: is_int(...),
            show: $showImplementation,
            instanceName: 'integer'
        );
        
        $this->assertEquals('Integer 123', show(123));
    }

    public function testCanShowArray(): void
    {
        // todo: reset method container state?
        $this->assertEquals('[1.1,2.2,3.3]', show([1.1,2.2,3.3]));
    }

    public function testCanShowBoolean(): void
    {
        // todo: reset method container state?
        $this->assertEquals('[true,false]', show([true, false]));
    }
}
