<?php

namespace thgs\Functional\Typeclass;

/**
 * @template A
 */
interface ApplicativeInstance
{
    /**
     * pure :: a -> f a  
     *
     * @template A1
     * @param A1 $a
     * @return ApplicativeInstance<A1>
     */
    public static function pure(mixed $a): ApplicativeInstance;

    /**
     * (<*>) :: f (a -> b) -> f a -> f b 
     *
     * @template B1
     * @param ApplicativeInstance<A> $fa
     * @return ApplicativeInstance<B1>
     *
     * @todo Here we assume that $this is ApplicativeInstance<callable(A):B1>
     * but how we write that for static analysis? Or does not even matter?
     * This is a bit reverse from others as usually $this is `f a`.
     */
    public function sequence(ApplicativeInstance $fa): ApplicativeInstance;
}
