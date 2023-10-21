<?php

namespace thgs\Functional\Typeclass;

/**
 * class Functor
 *      fmap :: (a -> b)  ->  f a  ->  f b
 *
 *
 * @template F of FunctorInstance
 * @template A
 * @template B
 */
interface FunctorInstance
{
    /**
     *      fmap :: (a -> b)  ->  f a  ->  f b
     *
     * "f a" is the implementor of the interface, returns "self" as the functor remain the same
     * but changes from a -> b
     *
     *
     * @psalm-param pure-callable(A): B $f
     */
    public function fmap(callable $f): self;
}