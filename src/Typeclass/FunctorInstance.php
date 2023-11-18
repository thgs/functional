<?php

namespace thgs\Functional\Typeclass;

/**
 * class Functor
 *      fmap :: (a -> b)  ->  f a  ->  f b
 *
 * @template A
 */
interface FunctorInstance
{
    /**
     *      fmap :: (a -> b)  ->  f a  ->  f b
     *
     * "f a" is the implementor of the interface, returns "self" as the functor remain the same
     * but changes from a -> b
     *
     * @template B
     * @psalm-param pure-callable(A):B $f
     * @return FunctorInstance<B>
     */
    public function fmap(callable $f): FunctorInstance;
}