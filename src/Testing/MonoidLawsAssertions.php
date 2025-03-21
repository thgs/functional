<?php

namespace thgs\Functional\Testing;

trait MonoidWithMemptyLawsAssertions
{
    protected function assertIsMonoidWithMempty($memptyValue, \Closure $associative, mixed $x, mixed $y, mixed $z): void
    {
        // right identity
        $this->assertEquals($x, $associative($x, $memptyValue));

        // left identity
        $this->assertEquals($x, $associative($memptyValue, $x));
        
        // associativity
        $this->assertEquals(
            $associative($x, $associative($y, $z)),
            $associative($associative($x, $y), $z),
        );

        // todo: concatenation
        // mconcat = foldr (<>) mempty
    }

    // todo: add assertIsMonoidWithMconcat

    // todo: figure out which monoid you would define with mempty / mconcat?
    
    /**
     * Asserts that two variables are equal.
     *
     * @throws PHPUnit\Framework\ExpectationFailedException
     */
    abstract static function assertEquals(mixed $expected, mixed $actual, string $message = ''): void;
}
