<?php

namespace thgs\Functional\Typeclass;

/**
 * @template A
 */
interface SemigroupInstance
{
    /**
     * a <> b = sconcat (a :| [ b ])
     *
     * @param A $a
     * @param A $b
     * @return A
     */
    public static function associate($a, $b);

    /**
     * sconcat :: NonEmpty a -> a
     * sconcat (a :| as) = go a as where
     *   go b (c:cs) = b <> go c cs
     *   go b []     = b
     *
     * @todo this requires list?
     *
     * @param A $a
     * @return A
     */
    public static function sconcat($a);
}
