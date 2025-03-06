<?php

namespace thgs\Functional\Data;

use thgs\Functional\Typeclass\EqInstance;
use function thgs\Functional\partial;

/**
 * @template A
 * @template B
 * @implements EqInstance<Tuple<A,B>>
 */
class Tuple implements
    EqInstance
{
    /**
     * (,) :: a -> b -> (a, b)
     * @param A $a
     * @param B $b
     */
    public function __construct(
        private mixed $a,
        private mixed $b
    ) {}

    /**
     * (,) :: a -> b -> (a, b)
     * @param A $a
     * @param B $b
     * @return self<A,B>
     */
    public static function new(mixed $a, mixed $b): self
    {
        return new self($a, $b);
    }

    /**
     * @param A $a
     * @return self<A,A>
     */
    public static function dupe(mixed $a): self
    {
        return new self($a, $a);
    }

    /**
     * both :: (a -> b) -> (a, a) -> (b, b)
     *
     * @param callable(A):B $f
     * @param Tuple<A,A> $p
     * @return self<B,B>
     */
    public static function both(callable $f, Tuple $p): self
    {
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
     * @template C
     * @param callable(Tuple<A,B>):C $f
     * @return C
     */
    public function curry(callable $f, mixed $a, mixed $b): mixed
    {
        return partial ($f) (new self($a, $b));
    }

    /**
     * @template C
     * @param callable(A,B):C $f
     * @param Tuple<A,B>
     * @return C
     */
    public function uncurry(callable $f, Tuple $p): callable
    {
        return partial ($f) ($p->fst()) ($p->snd());
    }

    public function swap(): self
    {
        return new self($this->b, $this->a);
    }

    /**
     * @param EqInstance<Tuple<A,B>> $other
     */
    public function equals(EqInstance $other): bool
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        if (!$other instanceof Tuple) {
            throw new \TypeError('Expecting instance of ' . Tuple::class);
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
}
