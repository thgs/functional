<?php

namespace thgs\Functional;

use thgs\Functional\Typeclass\FunctorInstance as F;

/**
 * @template A
 * @template B
 *
 * @param callable $f
 * @psalm-param pure-callable(A): B $f
 *
 * @param F<A> $g
 * @return F<B>
 */
function fmap(callable $f, F $g): F {
    // call the instance method
    return $g->fmap($f);
}
