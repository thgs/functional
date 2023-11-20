<?php

namespace thgs\Functional\Proof;

use thgs\Functional\Typeclass\FunctorInstance;

/**
 * For now can just give a trait to be used in unit tests,
 * later can make it so client code can use it anywhere
 */
trait FunctorProof
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
}