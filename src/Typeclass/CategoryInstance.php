<?php

namespace thgs\Functional\Typeclass;

/**
 * @template A
 * @template B
 * @template C
 * @phpstan-type CatBC callable(B):C
 * @phpstan-type CatAB callable(A):B
 * @phpstan-type CatAC callable(A):C
 *
 * @todo phpstan does not allow `phpstan-type` annotations in methods?
 */
interface CategoryInstance
{
    /**
     * Returns the identity function of this category.
     *
     * @return callable(A):A
     */
    public static function id(): callable;

    /**
     * Returns the composition function of this category.
     *
     * @return callable(CatBC, CatAB):CatAC
     */
    public static function compose(): callable;
}
