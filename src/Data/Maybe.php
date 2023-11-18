<?php

namespace thgs\Functional\Data;

use thgs\Functional\Typeclass\EqInstance;
use thgs\Functional\Typeclass\FunctorInstance;
use thgs\Functional\Typeclass\ShowInstance;

use function thgs\Functional\show;

/**
 * @template A1
 * @implements FunctorInstance<A1>
 * @implements EqInstance<Maybe<A1>>
 * @implements ShowInstance<Maybe>
 *
 * ApplicativeFunctor<Maybe>
 * Monad<Maybe>
 */
class Maybe implements
    EqInstance,
    ShowInstance,
    FunctorInstance
    /*, ApplicativeFunctor, Monad */
{
    /**
     * @param Nothing|Just<A1> $x
     */
    public function __construct(private readonly Nothing|Just $x)
    {
    }

    /**
     * @return Nothing|Just<A1>
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
     * (Int -> Bool) -> f Int -> f Bool
     *
     * This class is Maybe Int. WE have a func (Int -> Bool)
     * result of fmap will be a Maybe Bool
     *
     *
     * @template B1
     * @param callable(A1):B1 $f
     * @return FunctorInstance<B1>
     */
    public function fmap(callable $f): FunctorInstance
    {
        if ($this->x instanceof Nothing) {
            return new Maybe(new Nothing());
        }

        return new Maybe(new Just($f($this->x->getValue())));
    }

    /**
     * @param EqInstance<Maybe<A1>> $other
     */
    public function equals(EqInstance $other): bool
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        if (!$other instanceof Maybe) {
            throw new \TypeError('Expecting instance of Maybe');
        }

        // todo: improve the equality here, otherwise could
        // return true when Maybe Int and Maybe Char are passed with 1 and '1'.
        // At the same time must be able to equal with objects (probably we want `==` and not `===`).

        return $this->getValue() == $other->getValue();
    }

    /**
     * @param EqInstance<Maybe<A1>> $other
     */
    public function notEquals(EqInstance $other): bool
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        if (!$other instanceof Maybe) {
            throw new \TypeError('Expecting instance of Maybe');
        }

        return !$this->equals($other);
    }

    public function __toString(): string
    {
        return show($this->x);
    }
}