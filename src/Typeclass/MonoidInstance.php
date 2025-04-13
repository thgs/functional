<?php

namespace thgs\Functional\Typeclass;

/**
 * @template M
 * @template A
 */
interface MonoidInstance
{
    /**
     * @return A
     */
    public function mempty(): mixed;

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
     * @param A $a
     * @param A $b
     * @return A
     */
    public function mappend(mixed $a, mixed $b): mixed;

    // todo: define mconcat -- needs list (OR Foldable?)
}
