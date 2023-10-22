<?php

namespace thgs\Functional\Data;

use thgs\Functional\Typeclass\EqInstance;
use thgs\Functional\Typeclass\FunctorInstance;
use thgs\Functional\Typeclass\ShowInstance;
use function thgs\Functional\show;

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
class Maybe implements
    EqInstance,
    ShowInstance,
    FunctorInstance
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
                                            // todo: use Composition ?
            $this->x instanceof Just        => new Just( $f ( $this->x->getValue() ) ),
        });
    }


    /**
     * @param Maybe $other
     */
    public function equals(EqInstance $other): bool
    {
        if (!$other instanceof Maybe) {
            throw new \TypeError('Expecting instance of Maybe');
        }

        return $this->getValue() == $other->getValue();
    }

    public function notEquals(EqInstance $other): bool
    {
        if (!$other instanceof Maybe) {
            throw new \TypeError('Expecting instance of Maybe');
        }

        return !$this->equals($other);
    }

    public function __toString(): string
    {
        return $this->x instanceof Nothing
            ? 'Nothing'
            // todo: $this->x->getValue() MUST be an instance of Show
            : 'Just ' . show($this->x->getValue());
    }
}