<?php

namespace thgs\Functional;

use thgs\Functional\Typeclass\Eq;
use thgs\Functional\Typeclass\Functor;
use thgs\Functional\Typeclass\Show;


/**
 * Here are all type class methods defined by this library.  All of those use
 * the MethodContainer if a value without an type class interface is not passed.
 */

/**
 * @template A
 * @template B
 * @param A $a
 * @param B $b
 */
function equals(mixed $a, mixed $b): bool
{
    // todo: type check between a and b ? Container should allow multiple values check?
    // might still allow users to not opt-in for strict equality ? would it work?
    // maybe just move it out of equals and put it to something like "isomorphicEquals"
    // Does haskell allows instance Eq Num String for example? Could be a guide.

    return Eq::equals($a, $b);
}

/**
 * @template A
 * @template B
 * @param A $a
 * @param B $b
 */
function notEquals(mixed $a, mixed $b): bool
{
    return Eq::notEquals($a, $b);
}

/**
 * @template AF
 * @template BF
 *
 * @param Composition<AF,BF>|\Closure(AF):BF|callable(AF):BF $f
 * @param F<AF>|\Closure(AF):mixed|callable(AF):mixed|mixed $g
 * @return F<BF>
 */
function fmap(Composition|\Closure|callable $f, mixed $g): mixed
{
    /**
     * @todo Since $f is callable, we can wrap it in Composition? And
     * that gives fmap(callable, F|callable) and possibly opens up the
     * way for fmap(F|callable, F|callable) because why not.
     *
     * instance Functor ((->) a)
     *   fmap = (.)
     *
     * "Using fmap over functions is just composition"
     *
     * ---
     *
     * So that we do not have to force implementations handle whether the passed
     * callable is indeed a Composition or not, we handle it here. Implementations
     * can opt-in/out of using a composition during their fmap() though.
     *
     * @var \Closure(AF):BF $f
     */
    $f = $f instanceof Composition
        ? unwrapC ($f)
        : ($f instanceof \Closure ? $f : \Closure::fromCallable($f));

    /**
     * Support callable
     */
    if (!$g instanceof F && is_callable($g)) {
        $g = c ($g);
    }

    return Functor::fmap($f, $g);
}

function show(mixed $a): string
{
    return Show::show($a);
}

