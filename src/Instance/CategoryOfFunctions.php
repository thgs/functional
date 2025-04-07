<?php

namespace thgs\Functional\Instance;

use thgs\Functional\Typeclass\CategoryInstance;
use function thgs\Functional\partial;

/**
 * This tries to implement:
 *
 * Category (->)
 * instance Category (->) where
 *   id = GHC.Internal.Base.id
 *  (.) = (GHC.Internal.Base..)
 *
 * Which is the Category of functions
 *
 * @implements CategoryInstance<\Closure>
 */
class CategoryOfFunctions implements CategoryInstance
{
    public static function id(): mixed
    {
        /**
         * @template A
         * @param A $x
         * @return A
         */
        $id = fn ($x) => $x;
        return $id;
    }

    /**
     * @template A
     * @template B
     * @template C
     *
     * @param \Closure(A):B $a
     * @param \Closure(B):C $b
     * @return \Closure(A):C
     */
    public static function compose(mixed $a, mixed $b): mixed
    {
        return fn ($x) => partial($a, partial($b, $x));
    }
}
