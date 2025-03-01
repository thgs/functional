<?php

namespace thgs\Functional\Data;

use thgs\Functional\Instance\Composition;
use thgs\Functional\Typeclass\ApplicativeInstance;
use thgs\Functional\Typeclass\EqInstance;
use thgs\Functional\Typeclass\FunctorInstance;
use thgs\Functional\Typeclass\MonadInstance;
use thgs\Functional\Typeclass\ShowInstance;

use function thgs\Functional\fmap;
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
    FunctorInstance,
    ApplicativeInstance,
    MonadInstance
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

        $composition = new Composition($f);
        return new Maybe(
            new Just( $composition($this->x->getValue()) )
        );
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

    public static function pure(mixed $a): ApplicativeInstance
    {
        return new Maybe(new Just($a));
    }

    /**
     * In truth, receives and returns a Maybe, but the instance interface does not allow us.
     * param Maybe $fab
     * return Maybe
     * However if I do that, then I cannot runtime type check, static analysis says we have
     * already typed it. But we have not really, any thing would pass PHP check in method
     * declaration.
     */
    public function sequence(ApplicativeInstance $fa): ApplicativeInstance
    {
        // runtime type check
        if (!$fa instanceof Maybe) {
            throw new \TypeError('Sequenced value (parameter) is not expected Maybe');
        }

        if ($this->x instanceof Nothing) {
            return new Maybe(new Nothing());
        }

        // for now this will do, until it is more clear
        $callable = $this->x->getValue();
        if (!is_callable($callable)) {
            throw new \TypeError('Cannot sequence as Maybe instance contains a non callable value.');
        }

        // todo: psalm is telling us off because of `pure-callable` instead of `callable`
        return fmap($callable, $fa); // alternatively could write $this->fmap($fab);
    }

    public static function inject(mixed $a): MonadInstance
    {
        return self::pure($a);
    }

    public function bind(callable $f): MonadInstance
    {
        // todo: is this implementation an "unsafe" bind? Could bind
        // with a function that injects into a different monad.

        return $this->x instanceof Nothing
            ? new Nothing() // no need for new really
            : $f($this->x->getValue());
    }
}
