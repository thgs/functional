<?php

namespace thgs\Functional;

use thgs\Functional\Data\Either;
use thgs\Functional\Data\Left;
use thgs\Functional\Data\Right;
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

/**
 * @template A
 * @template B
 * @template C
 *
 * @param callable(A): C $f
 * @param callable(B): C $g
 * @param Either $either
 * @return C
 */
function either(callable $f, callable $g, Either $either)
{
    $value = $either->getValue();
    return match (\true) {
        $value instanceof Left => $f($value->getValue()),
        $value instanceof Right => $g($value->getValue())
    };
}