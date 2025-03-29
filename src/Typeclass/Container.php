<?php

namespace thgs\Functional\Typeclass;

use thgs\Functional\Data\Just;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Data\Nothing;

use function thgs\Functional\c;

/**
 * Contains type class implementations here
 */
class Container
{
    /**
     * @var array<string, array{predicate: \Closure, impl: \Closure}>
     */
    private static array $definitions;

    /**
     * @param \Closure(mixed):bool $predicate
     * @param \Closure $implementation
     *
     * @todo Make the container a state monad?
     */
    public static function register(string $identifier, \Closure $predicate, \Closure $implementation): void
    {
        self :: $definitions [$identifier] [] = ['predicate' => $predicate, 'impl' => $implementation];
    }

    public function get(string $identifier, mixed $target): \Closure
    {
        if (!isset( self :: $definitions [$identifier] )) {
            throw new \Exception("Missing definition for $identifier");
        }

        foreach (self :: $definitions [$identifier] as ['predicate' => $predicate, 'impl' => $impl]) {
            if ($predicate($target)) {
                return $impl;
            }
        }

        throw new \Exception("No definition for $identifier on given target");
    }
}

/**
 * Maybe we can still use interfaces like FunctorInstance but can also
 * provide the ability to register more fancy things that otherwise
 * would end up in `Instance` namespace by using the callable
 * predicate?
 *
 * This will work best when we can compile/build, so it changes to
 * static dispatch.
 *
 * The main benefit is that the type declaration does not have
 * to define interfaces OR be wrapped around to be used.
 *
 * example: Register `fmap` for some new X object that some imported
 * lib defines and then call fmap. Otherwise we would have to use
 * the `FunctorAdapter` or the `Wrapper` or implement a new class to
 * basically wrap around the X object and provide `fmap`.
 *
 * @see https://terbium.io/2021/02/traits-typeclasses/
 */
Container::register(
    'fmap',
    fn ($x) => $x instanceof Maybe,
    /**
     * @template A1
     * @template B1
     * @param \Closure(A1):B1 $f
     * @param Maybe<A1> $fa
     * @return Maybe<B1>
     */
    function (\Closure $f, Maybe $fa): Maybe {
        $value = $fa->getValue();
        if ($value instanceof Nothing) {
            return $fa;
        }

        /** @var Composition<A1,B1> */
        $c = c ($f);

        /** @phpstan-assert B1 $value->getValue() */

        return new Maybe(new Just( $c ($value->getValue()) ));
    }
);
