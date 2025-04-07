<?php

namespace thgs\Functional\Typeclass;

/**
 * @template cat
 *
 * @see https://hackage.haskell.org/package/ghc-internal-9.1201.0/docs/src/GHC.Internal.Control.Category.html#Category
 * @todo see https://hackage.haskell.org/package/data-category-0.4/docs/src/Data-Category.html#Obj
 */
interface CategoryInstance
{
    /**
     * Represents the identity morphism of this category.
     *
     * @return cat
     */
    public static function id(): mixed;

    /**
     * Returns the composition morphism of this category.
     *
     * @param cat $a
     * @param cat $b
     * @return cat
     */
    public static function compose(mixed $a, mixed $b): mixed;
}
