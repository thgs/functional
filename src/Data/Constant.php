<?php

namespace thgs\Functional\Data;

use thgs\Functional\Typeclass\FunctorInstance;

/**
 * @template C
 * @template A
 *
 * @implements FunctorInstance<Constant<C,*>>
 */
class Constant implements
    FunctorInstance
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
}
