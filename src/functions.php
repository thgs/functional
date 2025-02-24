<?php

namespace thgs\Functional;

use thgs\Functional\Data\Either;
use thgs\Functional\Data\Just;
use thgs\Functional\Data\Left;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Data\Nothing;
use thgs\Functional\Data\Right;
use thgs\Functional\Typeclass\EqInstance;
use thgs\Functional\Typeclass\FunctorInstance as F;
use thgs\Functional\Typeclass\ShowInstance;

function equals(EqInstance $a, EqInstance $b): bool
{
    return $a->equals($b);
}

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
    /**
     * @todo Since $f is callable, we can wrap it in Composition? And
     * that gives fmap(callable, F|callable) and possibly opens up the
     * way for fmap(F|callable, F|callable) because why not.
     *
     * instance Functor ((->) a)
     *   fmap = (.)
     *
     * "Using fmap over functions is just composition"
     */
    return $g->fmap($f);
}

/**
 * @param int|string|float|bool|ShowInstance $x
 */
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

/**
 * @template A
 * @template B
 * @param B $default
 * @param callable(A): B $f
 * @param Maybe $maybe
 * @return B
 */
function maybe($default, callable $f, Maybe $maybe)
{
    $value = $maybe->getValue();
    return match (\true) {
        $value instanceof Nothing => $default,
        $value instanceof Just => $f($value->getValue())
    };
}
