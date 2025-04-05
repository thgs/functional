<?php

namespace thgs\Functional\Data;

use thgs\Functional\Typeclass\FunctorInstance;
use function thgs\Functional\c;

/**
 * @template A
 * @implements FunctorInstance<Identity<A>>
 */
class Identity implements
    FunctorInstance
{
    public function __construct(
        /** @var A */
        private mixed $a
    ) {
    }

    /**
     * @template B1
     * @param \Closure(A):B1 $f
     * @return Identity<B1>
     */
    public function fmap(\Closure $f): FunctorInstance
    {
        return new self( c ($f) ($this->a) );
    }
}
