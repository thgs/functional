<?php

namespace thgs\Functional;

use thgs\Functional\Control\Typeclass\MonadInstance;
use thgs\Functional\Data\Either;
use thgs\Functional\Data\Left;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Data\Nothing;
use thgs\Functional\Data\Tuple3;
use thgs\Functional\Data\Tuple;
use thgs\Functional\Expression\Composition;
use thgs\Functional\Instance\CategoryOfFunctions;
use thgs\Functional\Instance\LeftToRightNotation;
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
 * @template AF
 * @template BF
 *
 * @param Composition<AF,BF>|\Closure(AF):BF|callable(AF):BF $f
 * @param F<AF>|\Closure(AF):mixed|callable(AF):mixed $g
 * @return F<BF>
 */
function fmap(Composition|\Closure|callable $f, F|\Closure|callable $g): F {
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
 * @return \Closure(R):A
 */
function unwrapC(Composition $composition): \Closure
{
    return Composition::unwrap($composition);
}

/**
 * Helper to create a Composition, in a possibly cryptic but concise way.
 *
 * @template R
 * @template A
 * @param \Closure(R):A|callable(R):A $f
 * @return Composition<R,A>
 */
function c(\Closure|callable $f): Composition
{
    return new Composition($f instanceof \Closure ? $f : \Closure::fromCallable($f));
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

function lefts(Either ...$eithers): iterable
{
    foreach ($eithers as $either) {
        if ($either->isRight()) {
            continue;
        }
        yield $either;
    }
}

function rights(Either ...$eithers): iterable
{
    foreach ($eithers as $either) {
        if ($either->isRight()) {
            yield $either;
        }
    }
}

/**
 * @template A
 * @template B
 * @param A $default
 * @param Either<A,B> $either
 * @return A
 */
function fromLeft($default, Either $either): mixed
{
    return $either->isRight() ? $default : $either->getValue()->getValue();
}

/**
 * @template A
 * @template B
 * @param B $default
 * @param Either<A,B> $either
 * @return B
 */
function fromRight($default, Either $either): mixed
{
    return $either->isRight() ? $either->getValue()->getValue() : $default;
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


/**
 * A draft implementation of do-notation. Use \Closure to indicate
 * (>>) `then` sequence and MonadInstance to indicate (>>=) `bind`
 * sequence.
 *
 * @template A
 * @template B
 * @param MonadInstance<A> $ma
 * @param \Closure(A):MonadInstance<B>|MonadInstance<B> $fs
 * @return MonadInstance<*>
 *
 * @see https://en.wikibooks.org/wiki/Haskell/do_notation
 *
 * @todo support Composition
 */
function dn(MonadInstance $ma, MonadInstance|\Closure/*|Composition*/ ...$fs)
{
    $last = $ma;
    foreach ($fs as $k => $new) {
        $last = $new instanceof \Closure // || $new instanceof Composition
            ? $last->bind($new)
            : $last->then($new);

        // Pedantic type check below
        if (!$last instanceof MonadInstance) {
            throw new \TypeError('Result is not a monad instance');
        }
    }
    return $last;
}

/**
 * A older bind-only implementation of do-notation
 *
 * @template A
 * @template B
 * @param MonadInstance<A> $ma
 * @param \Closure(A):MonadInstance<B> ...$fs
 * @return MonadInstance<*>
 */
function doBind(MonadInstance $ma, \Closure ...$fs)
{
    $last = $ma;
    foreach ($fs as $new) {
        $last = $last->bind($new);

        // Pedantic type check below
        if (!$last instanceof MonadInstance) {
            throw new \TypeError('Result is not a monad instance');
        }
    }
    return $last;
}

/**
 * @template A
 * @template B
 * @param A $a
 * @param B $b
 * @return Tuple<A,B>
 */
function t(mixed $a, mixed $b): Tuple
{
    return new Tuple($a, $b);
}

/**
 * @template A
 * @template B
 * @template C
 * @param A $a
 * @param B $b
 * @param C $c
 * @return Tuple3<A,B,C>
 */
function t3(mixed $a, mixed $b, mixed $c): Tuple3
{
    return new Tuple3($a, $b, $c);
}

/**
 * Draft implementation of memoize.
 *
 * Issues:
 * 1. Supports only memoizing the first argument and only single argument functions.
 *    Maybe through an object storage we could abstract over the multiple arguments,
 *    if "abstract" is the right word here, ie use a tuple and implement what is
 *    mentioned in Issue 2. It feels like currying here might be the way to go. Below
 *    already does some form of it with `partial`.
 *
 *    Two ways (at least) to see a -> a -> a
 *    - takes 2 arguments and returns type a
 *    - function that takes a single argument of type a and returns a function
 *      that takes another single argument of type a and returns type a
 *      ie. a -> (a -> a)
 *    - Finally, (a -> a) -> a IS our actual type signature here already.
 *
 * 2. Restricts first argument to be a scalar (must be able to form array key).
 *    Could reflect and have a few versions that support other types of arguments.
 *    However this gets harder and harder if we consider cases like objects as
 *    keys that need to implement EqInstance and define a way to lookup that
 *    array/storage through EqInstance equality. Otherwise it will not memoize
 *    effectively in all cases (ie when the instanceid is not the desired equality)
 *
 * 3. Returning function loses runtime type checks on the argument.
 *    This is not 100% true as we call the function at least once per argument
 *    so the initial wrapped function, if it carries type checks we will run them
 *    then and only successful calls will be memoized. Static analysis can help
 *    to pick up a wrong construction.
 *
 * 4. More? Must be more
 *
 * @see https://bartoszmilewski.com/2014/11/24/types-and-functions/ Challenge 1
 *
 * @template A
 * @template B
 * @param \Closure(A):B $f
 * @return Composition<A,B>
 */
function memoize(\Closure $f): Composition
{
    /** @var \Closure(A):B $wrapped */
    $wrapped = static function ($x) use ($f) {
        static $memoization = [];

        if (!isset($memoization[$x])) {
            $memoization[$x] = partial ($f, $x);
        }
        return $memoization[$x];
    };
    return c($wrapped);
}


function comp(\Closure $f, \Closure $g): \Closure
{
    return CategoryOfFunctions::compose ($f, $g);
}

/**
 * @todo this is broken
 * Usage:
 *
 *   rlc() ($f , $g)
 *
 * @todo I think here could assume multiple parameters and perform some
 * inlining, ie make a single closure that calls them all in order.
 * @todo define lrc
 */
function rlc(): callable
{
    // this is just a synonym for (.) which is right above.
    // for fun we can define it "point free"
    return CategoryOfFunctions::compose();
}

function functionComposition(\Closure ...$functions): \Closure
{
    $notation = new LeftToRightNotation(new CategoryOfFunctions());
    return $notation->composeMany(...$functions);
}

/**
 * This is a utility function. It is probably always better to do this
 * manually if you can. This is provided in case you need to turn a
 * list of callables to \Closure for example.
 */
function ensureClosure(callable $f): \Closure
{
    return $f instanceof \Closure ? $f : \Closure::fromCallable($f);
}


/**
 * @template A
 * @template B
 * @param \Closure(A):B $f
 * @return \Closure(B):A
 * This is like a contramap with Op
 */
function flip(\Closure $f): \Closure
{
    return fn ($x, $y) => $f($y, $x);
}
