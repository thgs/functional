<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Typeclass\Adapter\FunctorAdapter;

class FunctorAdapterTest extends TestCase
{
    public function testCanAdapt(): void
    {
        $toWrap = new class ('prefix_', 123) {
            public function __construct(private string $prefix, private $data)
            {
            }

            public function transformData(callable $f): self
            {
                return new self($this->prefix, $f($this->data));
            }

            public function build(): string
            {
                return $this->prefix . $this->data;
            }
        };

        $functor = new FunctorAdapter($toWrap, 'transformData');

        $add2 = fn (int $x): int => $x + 2;

        $result = $functor->fmap($add2)->fmap($add2);

        $this->assertEquals('prefix_127', $result->build());
    }
}