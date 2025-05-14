<?php

namespace thgs\Functional\Data;

use thgs\Functional\Control\Typeclass\ApplicativeInstance;
use thgs\Functional\Control\Typeclass\MonadInstance;
use thgs\Functional\Expression\Composition;
use thgs\Functional\Typeclass\Eq1Instance;
use thgs\Functional\Typeclass\EqInstance;
use thgs\Functional\Typeclass\FunctorInstance;
use thgs\Functional\Typeclass\MonoidInstance;
use thgs\Functional\Typeclass\SemigroupInstance;
use thgs\Functional\Typeclass\ShowInstance;

use function thgs\Functional\assoc;
use function thgs\Functional\c;
use function thgs\Functional\equals;
use function thgs\Functional\fmap;
use function thgs\Functional\just;
use function thgs\Functional\nothing;
use function thgs\Functional\show;

/**
 * @template A1
 * @implements FunctorInstance<A1>
 * @implements EqInstance<Maybe<A1>>
 * @implements Eq1Instance<Maybe<A1>>
 * @implements ShowInstance<Maybe<A1>>
 * @implements ApplicativeInstance<Maybe<A1>>
 * @implements MonadInstance<Maybe<A1>>
 * @implements SemigroupInstance<Maybe<A1>>
 * @implements MonoidInstance<Maybe<A1>>
 */
class Maybe implements
    EqInstance,
    Eq1Instance,
    ShowInstance,
    FunctorInstance,
    ApplicativeInstance,
    MonadInstance,
    SemigroupInstance,
    MonoidInstance
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

        return equals($this->getValue(), $other->getValue());
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

    /**
     * @template X
     * @param \Closure(A1,X):bool $eq
     * @param Maybe<X> $other
     */
    public function liftEq(\Closure $eq, mixed $other): bool
    {
        /**
         * For now we TypeError.
         * @phpstan-ignore instanceof.alwaysTrue
         */
        if (!$other instanceof Maybe) {
            throw new \TypeError('Expected instance of Maybe');
        }

        if ($this->isJust() && $other->isJust()) {
            return $eq($this->getValue()->getValue(), $other->getValue()->getValue());
        }

        return !$this->isJust() && !$other->isJust();
    }

    /**
     * @param Maybe<A1> $other
     * @return Maybe<A1>
     */
    public function assoc(SemigroupInstance $other): SemigroupInstance
    {
        /** @phpstan-ignore instanceof.alwaysTrue */
        if (!$other instanceof Maybe) {
            throw new \TypeError('Expected instance of Maybe');
        }

        if (!$this->isJust()) {
            return $other;
        }

        if (!$other->isJust()) {
            return $this;
        }

        return just(
            assoc($this->x->getValue(), $other->x->getValue()));
    }

    /**
     * @return A1
     */
    public static function sconcat($nonEmpty): mixed
    {
        throw new \Exception('Not implemented yet, waiting for Foldable/foldr');
    }

    public function mempty(): mixed
    {
        /**
         * @todo Is this correct? SA does not complain but should we return this?
         * @var Maybe<A1>
         */
        $nothing = nothing();
        return $nothing;
    }

    /**
     * @param Maybe<A1> $other
     * @return Maybe<A1>
     */
    public function mappend(MonoidInstance $other): MonoidInstance
    {
        return $this->assoc($other);
    }

    /**
     * @template A
     * @param A $a
     * @return Maybe<A>
     */
    public static function liftFromNullable(mixed $a): Maybe
    {
        return $a === null ? nothing() : just($a);
    }

    /**
     * @template A
     * @param A $a
     * @return Maybe<A>
     */
    public static function liftFromFalsy(mixed $a): Maybe
    {
        return $a ? just($a) : nothing();
    }
}
