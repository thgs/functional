<?php

namespace thgs\Functional\Data;

use thgs\Functional\Typeclass\BifunctorInstance;
use thgs\Functional\Typeclass\FunctorInstance;

use function thgs\Functional\c;

/**
 * @template C
 * @template A
 *
 * @implements FunctorInstance<Constant<C,*>>
 */
class Constant implements
    FunctorInstance,
    BifunctorInstance
{
    public function __construct(
        /** @var C */
        private mixed $c
    ) {
    }

    /**
     * @template B1
     * @param \Closure(A):B1 $f
     * @return Constant<C,B1>
     */
    public function fmap(\Closure $f): FunctorInstance
    {
        return new self($this->c);
    }

    public function bimap(\Closure $f, \Closure $g): BifunctorInstance
    {
        return new self(c ($f) ($this->c));
    }
}
