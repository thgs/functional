<?php

namespace thgs\Functional\Typeclass;

/**
 * @todo maybe move this into Data?
 *
 * class (forall a. Functor (p a)) => Bifunctor p where
 *
 * We use 2 templates for --simplicity--, for now.  In truth makes
 * things more complicated because when we implement we need to write
 * something like:
 *
 * implements BifunctorInstance<A,B>
 *
 * Instead of:
 *
 * implements BifunctorInstance<Either<A,B>>
 *
 * But I am not entirely sure how to do it yet with the templates
 * correctly because 'p' is only forming a Functor with all 'a'.
 *
 * @template A
 * @template C
 */
interface BifunctorInstance
{
    /**
     * bimap :: (a -> b) -> (c -> d) -> p a c -> p b d
     * Traditionally, `p a c` is the current instance
     *
     * @template B
     * @template D
     * @param \Closure(A):B $f
     * @param \Closure(C):D $g
     * @return BifunctorInstance<B,D>
     */
    public function bimap(\Closure $f, \Closure $g): BifunctorInstance;

    /**
     * @todo Create the other minimal definition with first, second. We could use
     * an abstract class?
     */
}
