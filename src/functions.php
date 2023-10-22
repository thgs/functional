<?php

namespace thgs\Functional;

use thgs\Functional\Typeclass\FunctorInstance as F;
use thgs\Functional\Typeclass\ShowInstance;

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
    // todo: since $f is callable, we can wrap it in Composition?
    return $g->fmap($f);
}

function show(int|string|float|bool|ShowInstance $x): string
{
    return (string) $x;
}