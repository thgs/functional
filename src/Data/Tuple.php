<?php

namespace thgs\Functional\Data;

use thgs\Functional\Typeclass\BifunctorInstance;
use thgs\Functional\Typeclass\EqInstance;
use function thgs\Functional\c;
use function thgs\Functional\partial;

/**
 * @template A
 * @template B
 *
 * @implements EqInstance<Tuple<A,B>>
 * @implements BifunctorInstance<A,B>
 */
class Tuple implements
    EqInstance,
    BifunctorInstance
{
    /**
     * (,) :: a -> b -> (a, b)
     *
     * @param A $a
     * @param B $b
     */
    public function __construct(
        private mixed $a,
        private mixed $b
    ) {}

    /**
     * (,) :: a -> b -> (a, b)
     *
     * @template A1
     * @template B1
     * @param A1 $a
     * @param B1 $b
     * @return self<A1,B1>
     */
    public static function new(mixed $a, mixed $b): self
    {
        return new self($a, $b);
    }

    /**
     * @template A1
     * @param A1 $a
     * @return self<A1,A1>
     */
    public static function dupe(mixed $a): self
    {
        return new self($a, $a);
    }

    /**
     * both :: (a -> b) -> (a, a) -> (b, b)
     *
     * @template A1
     * @template B1
     * @param \Closure(A1):B1 $f
     * @param Tuple<A1,A1> $p
     * @return Tuple<B1,B1>
     */
    public static function both(\Closure $f, Tuple $p): self
    {
        /**
         * Type override here for now.
         * @var \Closure(A1):B1 $g
         */
        $g = partial ($f);
        return new self($g ($p->fst()), $g ($p->snd()));
    }
    
    /** @return A */
    public function fst(): mixed
    {
        return $this->a;
    }

    /** @return B */
    public function snd(): mixed
    {
        return $this->b;
    }

    /**
     * @template A1
     * @template B1
     * @template C1
     * @param \Closure(Tuple<A1,B1>):C1 $f
     * @return C1
     */
    public static function curry(callable $f, mixed $a, mixed $b): mixed
    {
        /**
         * Temporary type override here and assignment.
         * @var \Closure(Tuple<A1,B1>):C1 $partialF
         */
        $partialF = partial ($f);
        return $partialF (new self($a, $b));
    }

    /**
     * @template A1
     * @template B1
     * @template C1
     * @param callable(A1,B1):C1 $f
     * @return callable(Tuple<A1,B1>):C1
     */
    public static function uncurry(callable $f)
    {
        /**
         * Temporary type override here and assignment.
         * @var callable(Tuple<A1,B1>):C1 $partialF
         */
        $partialF = partial (
            /**
             * @param Tuple<A1,B1> $p
             * @return C1
             */
            fn (Tuple $p): mixed => $f($p->fst(), $p->snd())
        );
        return $partialF;
    }

    /**
     * @return self<B,A>
     */
    public function swap(): self
    {
        return new self($this->b, $this->a);
    }

    /**
     * @param EqInstance<*>|Tuple<*,*> $other
     */
    public function equals(EqInstance $other): bool
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        if (!$other instanceof Tuple) {
            return false;
        }
 
        /**
         * @todo consider this: This is implemented with fst() and
         * snd() to allow any extension, however if the class is not
         * the same, are they really the same?
         *
         * @todo also what if the tuple contains a closure and the
         * other tuple contains the same closure functionally but a
         * different instance of it
         */
        return $this->fst() == $other->fst()
            && $this->snd() == $other->snd();
    }

    public function notEquals(EqInstance $other): bool
    {
        return !$this->equals($other);
    }

    public function bimap(\Closure $f, \Closure $g): BifunctorInstance
    {
        return new self(
            c ($f) ($this->fst()),
            c ($g) ($this->snd())
        );
    }
}
