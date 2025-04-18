<?php

namespace thgs\Functional\Data;

use thgs\Functional\Control\Typeclass\ApplicativeInstance;
use thgs\Functional\Control\Typeclass\MonadInstance;
use thgs\Functional\Expression\Composition;
use thgs\Functional\Typeclass\EqInstance;
use thgs\Functional\Typeclass\FunctorInstance;
use thgs\Functional\Typeclass\ShowInstance;

use function thgs\Functional\c;
use function thgs\Functional\fmap;
use function thgs\Functional\show;

/**
 * @template A1
 * @implements FunctorInstance<A1>
 * @implements EqInstance<Maybe<A1>>
 * @implements ShowInstance<Maybe<A1>>
 * @implements ApplicativeInstance<Maybe<A1>>
 * @implements MonadInstance<Maybe<A1>>
 */
class Maybe implements
    EqInstance,
    ShowInstance,
    FunctorInstance,
    ApplicativeInstance,
    MonadInstance
{
    public function __construct(
        /**
         * @var Nothing|Just<A1> $x
         */
        private readonly Nothing|Just $x
    ) {}

    /**
     * @return Nothing|Just<A1>
     */
    public function getValue(): Nothing|Just
    {
        return $this->x;
    }

    /**
     * @phpstan-assert-if-true Just<A1> $this->x
     * @phpstan-assert-if-true Just<A1> $this->getValue()
     * @phpstan-assert-if-false Nothing $this->x
     */
    public function isJust(): bool
    {
        return $this->x instanceof Just;
    }

    /**
     * @return A1|null
     */
    public function unwrap()
    {
        return $this->x->getValue();
    }

    /**
     * The instance of Maybe plays the role of `f a` in
     *
     *   fmap :: (a -> b) -> f a -> f b
     *
     * Therefore given a (a -> b) this will return a `f b`
     *
     * @template B1
     * @param \Closure(A1):B1 $f
     * @return Maybe<B1>
     */
    public function fmap(\Closure $f): Maybe
    {
        if ($this->x instanceof Nothing) {
            return $this;
        }
        /** @var Composition<A1,B1> */
        $c = c ($f);

        /** @phpstan-assert B1 $this->x->getValue() */
        $x = $this->x->getValue();

        return new Maybe(new Just( $c ($x) ));
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

    /**
     * @template X
     * @param X $a
     * @return Maybe<X>
     */
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

        /** @phpstan-assert Just<A> $this->x */

        $callable = $this->x->getValue();
        /**
         * `callable` is allowed here to support Maybe<callable>
         */
        if (!is_callable($callable)) {
            throw new \TypeError('Cannot sequence as Maybe instance contains a non callable value.');
        }

        $closure = !$callable instanceof \Closure ? \Closure::fromCallable($callable) : $callable;
        return fmap($closure, $fa); // alternatively could write $this->fmap($fab);
    }

    /**
     * @template X
     * @param X $a
     * @return Maybe<X>
     */
    public static function inject(mixed $a): MonadInstance
    {
        return self::pure($a);
    }

    public function bind(\Closure $f): MonadInstance
    {
        // todo: is this implementation an "unsafe" bind? Could bind
        // with a function that injects into a different monad.

        return $this->x instanceof Nothing
            ?  new Maybe($this->x) // no need for new really
            : c ($f) ($this->x->getValue());
    }

    public function then(MonadInstance $b): MonadInstance
    {
        return $this->bind(fn () => $b);
    }
}
