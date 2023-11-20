<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Proof\FunctorProof;
use thgs\Functional\Typeclass\Adapter\FunctorAdapter;

class FunctorAdapterTest extends TestCase
{
    use FunctorProof;

    private function getDummy(): object
    {
        return new class ('prefix_', 123) {
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
    }

    public function testIsAFunctor(): void
    {
        $subject = new FunctorAdapter($this->getDummy(), 'transformData');

        $this->assertInstanceIsFunctor(
            $subject,
            fn (int $x): int => $x + 2,
            fn (int $x): int => $x + 2
        );
    }

    public function testCanAdapt(): void
    {
        $functor = new FunctorAdapter($this->getDummy(), 'transformData');

        $add2 = fn (int $x): int => $x + 2;
        $result = $functor->fmap($add2)->fmap($add2);

        $this->assertEquals('prefix_127', $result->build());

        $this->assertInstanceIsFunctor(
            $result,
            fn (int $x): int => $x + 2,
            fn (int $x): int => $x + 2
        );
    }
}