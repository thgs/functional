<?php

namespace thgs\Functional\Data;

use thgs\Functional\Typeclass\BifunctorInstance;

use function thgs\Functional\c;
use function thgs\Functional\partial;

/**
 * @template A
 * @template B
 * @template C
 */
class Tuple3 implements
    BifunctorInstance
{
    /**
     * @param A $a
     * @param B $b
     * @param C $c
     */
    public function __construct(
        private mixed $a,
        private mixed $b,
        private mixed $c
    ) {}
    
    /** @return A */
    public function fst3(): mixed
    {
        return $this->a;
    }

    /** @return B */
    public function snd3(): mixed
    {
        return $this->b;
    }

    /** @return C */
    public function thd3(): mixed
    {
        return $this->c;
    }

    /**
     * @template D
     * @param callable(Tuple3<A,B,C>):D $f
     * @return D
     */
    public function curry3(callable $f, mixed $a, mixed $b, mixed $c): mixed
    {
        return partial ($f) (new self($a, $b, $c));
    }

    /**
     * @template D
     * @param callable(A,B,C):D $f
     * @return D
     */
    public function uncurry3(callable $f, Tuple3 $p): callable
    {
        return partial ($f) ($p->fst3()) ($p->snd3()) ($p->thd3());
    }

    public function bimap(\Closure $f, \Closure $g): BifunctorInstance
    {
        return new self(
            $this->fst3(),
            c ($f) ($this->snd3()),
            c ($g) ($this->thd3()),
        );
    }
}
