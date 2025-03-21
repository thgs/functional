<?php

namespace thgs\Functional\Typeclass;

/**
 * @template M
 *
 * @todo implement MonoidInstance in terms of Semigroup.
 * Haskell's interface to making a Monoid is to define types and
 * override defaults of either mempty or mconcat. mappend is defined
 * in terms of Semigroup. I think `template M of SemigroupInstance`
 * would be what we are looking for here so that mappend can have a
 * default implementation of returning the associative function from
 * the Semigroup.
 */
interface MonoidInstance
{
    /**
     * @return M
     */
    public static function mempty(): mixed;

    /**
     * Returns the associative function of the monoid, to allow type
     * declarations in the function.
     *
     * Usage should be:
     *
     * MonoidInstance::mappend() ("Hello", " world!");
     *
     * instead of:
     *
     * MonoidInstance::mappend ("Hello", " world!");
     *
     * @return \Closure(M,M):M
     */
    public static function mappend(): \Closure;

    // todo: define mconcat -- needs list (OR Foldable?)
}
