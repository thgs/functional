<?php

namespace thgs\Functional\Typeclass;

/**
 * @template A
 */
interface FoldableInstance
{
    /**
     * Haskell's foldMap :: Monoid m => (a -> m) -> t a -> m
     *
     * `t a` is the instance implementing this interface.  Note that
     * `m` could be very much the same as `a`, just indicates the
     * monoid instance.
     *
     * @template M
     * @param \Closure(A):M $f
     * @return M
     */
    public function foldMap(\Closure $f): mixed;

    /**
     * Haskell's foldr :: (a -> b -> b) -> b -> t a -> b
     *
     * `t a` is the instance implementing this interface.
     *
     * @template B
     * @param \Closure(A,B):B $f
     * @param B $b
     * @return B
     */
    public function foldr(\Closure $f, mixed $b): mixed;
}
