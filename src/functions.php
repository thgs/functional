<?php

namespace thgs\Functional;

use thgs\Functional\Container\Container;
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
use thgs\Functional\Typeclass\Eq1;
use thgs\Functional\Typeclass\Eq1Instance;
use thgs\Functional\Typeclass\EqInstance;

/**
 * @template A
 * @param A $a
 * @return A
 */
function id(mixed $a): mixed
{
    return $a;
}


/**
 * @template A
 * @template B
 * @param A $a
 * @param B $b
 * @return A
 */
function const_(mixed $a, mixed $b): mixed
{
    return $a;
}


/**
 * @template A
 * @param A $a
 * @return null
 */
function unit(mixed $a = null): null
{
    return null;
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
 * Right to left function composition
 */
function rl(\Closure|callable ...$xs): \Closure
{
    // @todo improve this, inline the notation, test it

    $xs = array_map(ensureClosure(...), $xs);

    return (new LeftToRightNotation(new CategoryOfFunctions()))
        ->composeMany(...array_reverse($xs));
}


/**
 * Left to right function composition
 */
function lr(\Closure|callable ...$xs): \Closure
{
    // @todo improve this, inline the notation, test it

    $xs = array_map(ensureClosure(...), $xs);

    return (new LeftToRightNotation(new CategoryOfFunctions()))
        ->composeMany(...$xs);
}


/**
 * ($) :: (a -> b) -> a -> b
 *
 * This is at this point a synonym for partial(), however the number
 * of arguments that can be applied is fixed (1).
 *
 * @todo consider a notation here? possibly (&) as well?
 *
 * @template A
 * @template B
 * @param \Closure(A):B $f
 * @param A $a
 * @return B
 */
function apply(\Closure $f, mixed $a): mixed
{
    return partial($f, $a);
}


/**
 * Helper to create a Composition, in a possibly cryptic but concise way.
 *
 * @deprecated
 *
 * @template R
 * @template A
 * @param \Closure(R):A|callable(R):A $f
 * @return Composition<R,A>
 */
function c(\Closure|callable $f): Composition
{
    // todo: last resort type-hint below
    /** @var Composition<R,A> */
    $r = new Composition($f instanceof \Closure ? $f : \Closure::fromCallable($f));
    return $r;
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
 * @template A1
 * @template B1
 * @param Either<A1,B1> ...$eithers
 * @return \Generator<array-key, Either<A1,B1>>
 */
function lefts(Either ...$eithers): iterable
{
    foreach ($eithers as $either) {
        if ($either->isRight()) {
            continue;
        }
        yield $either;
    }
}


/**
 * @template A1
 * @template B1
 * @param Either<A1,B1> ...$eithers
 * @return \Generator<array-key, Either<A1,B1>>
 */
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
 * @param \Closure(A):MonadInstance<B>|MonadInstance<B> $f
 * @param \Closure(A):MonadInstance<B>|MonadInstance<B> ...$fs
 * @return MonadInstance<B>
 *
 * @see https://en.wikibooks.org/wiki/Haskell/do_notation
 *
 * @todo support Composition
 */
function dn(MonadInstance $ma, MonadInstance|\Closure $f, MonadInstance|\Closure/*|Composition*/ ...$fs)
{
    $last = $ma;
    array_unshift($fs, $f);

    foreach ($fs as $k => $new) {
        $last = $new instanceof \Closure // || $new instanceof Composition
            ? $last->bind($new)
            : $last->then($new);

        /**
         * The MonadInstance's bind() and then() require the return
         * value to be MonadInstance.
         *
         * @todo support dn() through bind() and then() using the
         * container.
         *
         * @todo removing this check from the implementation, we can
         * bind with a function that returns a different monad
         * instance.
         */
    }
    return $last;
}


/**
 * A older bind-only implementation of do-notation
 *
 * @template A
 * @template B
 * @param MonadInstance<A> $ma
 * @param \Closure(A):MonadInstance<B> $f
 * @param \Closure(A):MonadInstance<B> ...$fs
 * @return MonadInstance<*>
 */
function doBind(MonadInstance $ma, \Closure $f, \Closure ...$fs)
{
    $last = $ma;
    array_unshift($fs, $f);

    foreach ($fs as $new) {
        $last = $last->bind($new);
    }
    return $last;
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
 * @todo this may need to be "pure-\Closure", if supported?
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
        /** @var mixed[] */
        static $memoization = [];

        if (!isset($memoization[$x])) {
            $memoization[$x] = partial ($f, $x);
        }
        return $memoization[$x];
    };
    return c($wrapped);
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
 * @template C
 * @param \Closure(A,B):C $f
 * @return \Closure(B,A):C
 * This is like a contramap with Op
 */
function flip(\Closure $f): \Closure
{
    return fn ($x, $y) => $f ($y, $x);
}


/**
 * From Eq1
 */


/**
 * @template A
 * @template Fa
 * @param Eq1Instance<A>|Fa $fa1
 * @param Eq1Instance<A>|Fa $fa2
 */
function eq1(mixed $fa1, mixed $fa2): bool
{
    return Eq1::liftEq(equals(...), $fa1, $fa2);
}

