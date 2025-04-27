<?php

namespace thgs\Functional\Data;

use thgs\Functional\Typeclass\BifunctorInstance;
use thgs\Functional\Typeclass\FunctorInstance;

use function thgs\Functional\c;

/**
 * @template C1
 * @template A1
 *
 * @implements FunctorInstance<Constant<C1,*>>
 * @implements BifunctorInstance<C1,A1>
 */
class Constant implements
    FunctorInstance,
    BifunctorInstance
{
    public function __construct(
        /** @var C1 */
        private mixed $c
    ) {
    }

    /**
     * @template B1
     * @param \Closure(A1):B1 $f
     * @return Constant<C1,B1>
     */
    public function fmap(\Closure $f): FunctorInstance
    {
        /**
         * Not sure how could this be fixed and carry the B1, apart
         * from overriding it with a var annotation.  Instead, going
         * to suppress the error and assume static analysis will
         * resolve from the return annotation of the method.
         *
         * @phpstan-ignore return.type
         */
        return new self($this->c);
    }

    /**
     * @template B
     * @template D
     * @param \Closure(A1):B $f
     * @param \Closure(C1):D $g
     * @return Constant<B,D>
     */
    public function bimap(\Closure $f, \Closure $g): BifunctorInstance
    {
        /**
         * Not sure how could this be fixed and carry the B1, apart
         * from overriding it with a var annotation.  Instead, going
         * to suppress the error and assume static analysis will
         * resolve from the return annotation of the method.
         *
         * @phpstan-ignore return.type
         */
        return new self(c ($f) ($this->c));
    }
}
