<?php

namespace thgs\Functional\Testing;

use thgs\Functional\Typeclass\FunctorInstance;

/**
 * For now can just give a trait to be used in unit tests,
 * later can make it so client code can use it anywhere
 */
trait FunctorLawsAssertions
{
    protected function assertInstanceIsFunctor(
        FunctorInstance $subject,
        callable $f,
        callable $g
    ): void {
        $this->assertFunctorInstanceMapsId($subject);
        $this->assertFunctorInstanceIsAssociative($subject, $f, $g);
    }

    protected function assertFunctorInstanceMapsId(FunctorInstance $subject): void
    {
        $result = $subject->fmap(fn ($x) => $x);

        // todo: is this enough?
        $this->assertInstanceOf(get_class($subject), $result, 'result is not of the same class');
        $this->assertEquals($subject, $result, 'result is not the same after mapping');
    }

    /**
     * @todo is this enough to proove associativity?
     */
    protected function assertFunctorInstanceIsAssociative(
        FunctorInstance $subject,
        callable $f,
        callable $g
    ): void {
        $composition = fn ($x) => $f($g($x));

        // todo: is this enough?
        $this->assertEquals(
            $subject->fmap($composition),
            $subject->fmap($g)->fmap($f),
            'instance is not associative'
        );
    }

    /**
     * Asserts that two variables are equal.
     *
     * @throws PHPUnit\Framework\ExpectationFailedException
     */
    abstract static function assertEquals(mixed $expected, mixed $actual, string $message = ''): void;

    /**
     * Asserts that a variable is of a given type.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws UnknownClassOrInterfaceException
     *
     * @psalm-template ExpectedType of object
     *
     * @psalm-param class-string<ExpectedType> $expected
     *
     * @psalm-assert =ExpectedType $actual
     */
    abstract static function assertInstanceOf(string $expected, mixed $actual, string $message = ''): void;
}
