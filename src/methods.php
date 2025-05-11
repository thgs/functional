<?php

namespace thgs\Functional;

use thgs\Functional\Container\TypeName;
use thgs\Functional\Control\Typeclass\MonadInstance;
use thgs\Functional\Data\Ordering;
use thgs\Functional\Expression\Composition;
use thgs\Functional\Typeclass\Contravariant;
use thgs\Functional\Typeclass\ContravariantInstance;
use thgs\Functional\Typeclass\Eq;
use thgs\Functional\Typeclass\Eq1;
use thgs\Functional\Typeclass\Functor;
use thgs\Functional\Typeclass\FunctorInstance;
use thgs\Functional\Typeclass\Monad;
use thgs\Functional\Typeclass\Monoid;
use thgs\Functional\Typeclass\Ord;
use thgs\Functional\Typeclass\Semigroup;
use thgs\Functional\Typeclass\Show;


/**
 * Here are all type class methods defined by this library.  All of those use
 * the MethodContainer if a value without an type class interface is not passed.
 */


/**
 * ---------------------------------------- Eq
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
 * --------------------------------------- Eq1
 */


/**
 * @template A
 * @template B
 * @param \Closure(A,B):bool $eq
 * @param A $a
 * @param B $b
 */
function liftEq(\Closure $eq, mixed $a, mixed $b): bool
{
    return Eq1::liftEq($eq, $a, $b);
}


/**
 * ----------------------------------- Functor
 */


/**
 * @template AF
 * @template BF
 *
 * @param Composition<AF,BF>|\Closure(AF):BF|callable(AF):BF $f
 * @param FunctorInstance<AF>|\Closure(AF):mixed|callable(AF):mixed|mixed $g
 * @return FunctorInstance<BF>
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
    if (!$g instanceof FunctorInstance && is_callable($g)) {
        $g = c ($g);
    }

    return Functor::fmap($f, $g);
}


/**
 * ----------------------------- Contravariant
 */


/**
 * @template A2
 * @template B2
 * @template Ca1
 * @template Cb1
 * @param \Closure(B2):A2|callable(B2):A2 $f
 * @param ContravariantInstance<A2>|Ca1 $fa
 * @return ($fa is ContravariantInstance<A2> ? ContravariantInstance<B2> : Cb1)
 */
function contramap(\Closure|callable $f, mixed $fa): mixed
{
    /** @var \Closure(B2):A2 */
    $f = $f instanceof \Closure ? $f : \Closure::fromCallable($f);
    return Contravariant::contramap($f, $fa);
}


/**
 * -------------------------------------- Show
 */


function show(mixed $a): string
{
    return Show::show($a);
}


/**
 * ------------------------------------ Monoid
 */


function mappend(mixed $a, mixed $b, ?TypeName $asType = null): mixed
{
    return Monoid::mappend($a, $b, $asType);
}


function mempty(TypeName|string $asType): mixed
{
    return Monoid::mempty($asType);
}


/**
 * ------------------------------------- Monad
 */


/**
 * @template A2
 * @template B2
 * @template Ma2
 * @template Mb2
 *
 * @param MonadInstance<A2>|Ma2 $ma
 * @param \Closure(A2):MonadInstance<B2>|\Closure(A2):Mb2 $f
 * @return ($ma is MonadInstance<A2> ? MonadInstance<B2> : Mb2)
 */
function bind(mixed $ma, \Closure $f): mixed
{
    return Monad::bind($ma, $f);
}


function inject(mixed $a, TypeName $asType): mixed
{
    return Monad::inject($a, $asType);
}


function then(mixed $ma, mixed $mb): mixed
{
    return Monad::then($ma, $mb);
}


/**
 * --------------------------------------- Ord
 */


/**
 * @template A
 * @param A $a
 * @param A $b
 */
function compare(mixed $a, mixed $b): Ordering
{
    return Ord::compare($a, $b);
}


/**
 * @template A
 * @param A $a
 * @param A $b
 */
function lessOrEqual(mixed $a, mixed $b): bool
{
    return Ord::lessOrEqual($a, $b);
}


/**
 * @template A
 * @param A $a
 * @param A $b
 */
function less(mixed $a, mixed $b): bool
{
    return Ord::less($a, $b);
}


/**
 * @template A
 * @param A $a
 * @param A $b
 */
function moreOrEqual(mixed $a, mixed $b): bool
{
    return Ord::moreOrEqual($a, $b);
}


/**
 * @template A
 * @param A $a
 * @param A $b
 */
function more(mixed $a, mixed $b): bool
{
    return Ord::more($a, $b);
}


/**
 * @template A
 * @param A $a
 * @param A $b
 * @return A
 */
function max(mixed $a, mixed $b): mixed
{
    return Ord::max($a, $b);
}


/**
 * @template A
 * @param A $a
 * @param A $b
 * @return A
 */
function min(mixed $a, mixed $b): mixed
{
    return Ord::min($a, $b);
}


/**
 * --------------------------------- Semigroup
 */


/**
 * @template A
 * @param A $a
 * @param A $b
 * @return A
 */
function assoc(mixed $a, mixed $b): mixed
{
    return Semigroup::assoc($a, $b);
}


/**
 * @template A
 * @param iterable<A> $nonEmpty
 * @return A
 */
function sconcat(iterable $nonEmpty): mixed
{
    return Semigroup::sconcat($nonEmpty);
}


// todo: add stimes
