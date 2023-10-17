<?php

namespace thgs\Functional\Data;

use thgs\Functional\Typeclass\FunctorInstance;

/**
 * @template A
 * @template B
 * @template-implements FunctorInstance<Maybe,A,B>
 *
 * ApplicativeFunctor<Maybe>
 * Monad<Maybe>
 *
 * todo: Type A must be whatever the Just<A> is?
 * ie "Maybe Int" is (Just Int | Nothing)
 *
 */
class Maybe
    implements FunctorInstance
    /*, ApplicativeFunctor, Monad */
{
    /**
     * @param Nothing|Just<A> $x
     */
    public function __construct(private readonly Nothing|Just $x)
    {
    }

    /**
     * @return Nothing|Just<A>
     */
    public function getValue(): Nothing|Just
    {
        return $this->x;
    }

    /**
     * instance Functor Maybe where
     *      fmap f (Just x) = Just (f x)
     *      fmap f Nothing = Nothing
     *
     * Here we need to implement fmap :: (a -> b) -> f b
     * as the `f a` we are.
     *
     *
     * @param pure-callable(A): B $f
     */
    public function fmap(callable $f): static
    {
        // below does not need "match", a ternary can do, but for brevity now
        return new static(match (true) {
            $this->x instanceof Nothing     => new Nothing(),
            $this->x instanceof Just        => new Just( $f ( $this->x->getValue() ) ),
        });
    }
}