<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Container\TypeName;

class TypeNameTest extends TestCase
{
    public function testCanEq(): void
    {
        $a = new TypeName('Something');
        $b = new TypeName('Something');

        $this->assertTrue($a->equals($b));
    }
}
