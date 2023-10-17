<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\Just;
use thgs\Functional\Data\Maybe;

class MaybeTest extends TestCase
{
    public function testFmap()
    {
        $data = new Maybe(new Just(3));
        $mapped = $data->fmap(fn (int $x) => $x + 2);

        $value = $mapped->getValue();
        $this->assertInstanceOf(Maybe::class, $mapped, 'result not an instance of Maybe');
        $this->assertInstanceOf(Just::class, $value, 'value not an instance of Just');
        $this->assertEquals(5, $value->getValue());
    }


    // todo: add tests for functor laws. could be generic/parameterized?
}