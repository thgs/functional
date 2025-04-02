<?php

namespace thgs\Functional\Control\Typeclass;

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
     * @template A1
     * @template B1
     * @phpstan-assert ApplicativeInstance<callable(A1):B1> $this
     * @param ApplicativeInstance<A1> $fa
     * @return ApplicativeInstance<B1>
     *
     * @todo Here we assume that $this is ApplicativeInstance<callable(A):B1>
     * but how we write that for static analysis? Or does not even matter?
     * This is a bit reverse from others as usually $this is `f a`.
     * Maybe a static that takes 2 arguments would be better?
     */
    public function sequence(ApplicativeInstance $fa): ApplicativeInstance;
}
