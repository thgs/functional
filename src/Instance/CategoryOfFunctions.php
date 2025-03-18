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
 * @template A
 * @template B
 * @template C
 * @implements CategoryInstance<A,B,C>
 */
class CategoryOfFunctions implements CategoryInstance
{
    /**
     * @todo this feels like something is missing.
     * @return callable(A):A
     */
    public static function id(): callable
    {
        return fn ($x) => $x;
    }

    public static function compose(): callable
    {
        return fn ($f, $g) => fn ($x) => partial($f (partial($g, $x)));
    }
}
