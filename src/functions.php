<?php

namespace thgs\Functional;

use thgs\Functional\Data\Either;
use thgs\Functional\Data\Just;
use thgs\Functional\Data\Left;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Data\Nothing;
use thgs\Functional\Data\Right;
use thgs\Functional\Instance\Composition;
use thgs\Functional\Typeclass\EqInstance;
use thgs\Functional\Typeclass\FunctorInstance as F;

/**
 * @template A of EqInstance
 * @template B of EqInstance
 * @param A $a
 * @param B $b
 */
function equals(EqInstance $a, EqInstance $b): bool
{
    return $a->equals($b);
}

/**
 * @template A
 * @template B
 *
 * @param Composition<A,B>|callable(A):B $f
 * @psalm-param Composition<A,B>|callable(A):B $f
 *
 * @param F<A>|callable(A):B $g
 * @return F<B>
 */
function fmap(Composition|callable $f, F|callable $g): F {
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
     * @var callable(A):B $f
     */
    $f = $f instanceof Composition ? unwrapC ($f) : $f;

    /**
     * Ensure $g has fmap
     */
    $g = $g instanceof F ? $g : c ($g);
    return $g ->fmap ($f);
}

/**
 * Helper to call Composition::unwrap with fewer keystrokes.
 * @template R
 * @template A
 * @param Composition<R,A> $composition
 */
function unwrapC(Composition $composition): callable
{
    return Composition::unwrap($composition);
}

/**
 * Helper to create a Composition, in a possibly cryptic but concise way.
 *
 * @template R
 * @template A
 * @param callable(R):A $f
 * @return Composition<R,A>
 */
function c(callable $f): Composition
{
    return new Composition($f);
}

function show(mixed $x): string
{
    // todo: phpstan says Dead catch here but `(string) $x` would throw
    // if $x is an object that does not implement ShowInstance/Stringable

    // todo: improve message

    try { return (string) $x; } catch (\Throwable $err) {
        throw new \TypeError('Cannot show ' . gettype($x));
    }
}

/**
 * @template A
 * @template B
 * @template C
 *
 * @param callable(A):C $f
 * @param callable(B):C $g
 * @param Either<A,B> $either
 * @return C
 */
function either(callable $f, callable $g, Either $either)
{
    $eitherValue = $either->getValue();
    return c ($eitherValue instanceof Left ? $f : $g) ($eitherValue->getValue());
}

/**
 * @template A
 * @template B
 * @param B $default
 * @param callable(A):B $f
 * @param Maybe<A> $maybe
 * @return B
 */
function maybe(mixed $default, callable $f, Maybe $maybe): mixed
{
    $maybeValue = $maybe->getValue();

    return $maybeValue instanceof Nothing
        ? $default
        : c ($f) ($maybeValue->getValue());
}

// todo: define `pure` & `sequence` from Applicative.
