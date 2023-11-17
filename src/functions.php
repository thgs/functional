<?php

namespace thgs\Functional;

use thgs\Functional\Data\Either;
use thgs\Functional\Data\Just;
use thgs\Functional\Data\Left;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Data\Nothing;
use thgs\Functional\Data\Right;
use thgs\Functional\Typeclass\Attribute\FunctorInstance as FunctorInstanceAttribute;
use thgs\Functional\Typeclass\Attribute\ShowInstance as ShowInstanceAttribute;
use thgs\Functional\Typeclass\FunctorInstance;
use thgs\Functional\Typeclass\ShowInstance;

use function thgs\Functional\Internal\getAttributeProperty;

/**
 * @template A
 * @template B
 *
 * @param callable $f
 * @psalm-param pure-callable(A): B $f
 *
 * @param FunctorInstance<A>|object|callable $g
 * @return FunctorInstance<B>|object|callable
 */
function fmap(callable $f, object|callable $g): object|callable {
    if ($g instanceof FunctorInstance) {
        // call the instance method
        // todo: since $f is callable, we can wrap it in Composition?
        return $g->fmap($f);
    }

    // todo: support marking functions as functors
    // see https://stackoverflow.com/questions/43379364/typeclass-instances-for-functions

    $fmapMethod = getAttributeProperty($g, FunctorInstanceAttribute::class, FunctorInstance::FMAP);

    return $g->$fmapMethod($f);

    // todo: support looking through methods if getAttributes on the reflObject is not doing it
}

/**
 * @param int|string|float|bool|ShowInstance|object $x
 */
function show(int|string|float|bool|object $x): string
{
    if (is_scalar($x) || $x instanceof ShowInstance | $x instanceof \Stringable) {
        return (string) $x;
    }

    // note: with the new type hint for $x we lose the type error so getAttributeProperty has to
    // raise a type error if the attribute is not there.
    $showMethod = getAttributeProperty($x, ShowInstanceAttribute::class, 'show');
    return $x->$showMethod();
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