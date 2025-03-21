<?php

namespace thgs\Functional\Typeclass;

/**
 * @template A
 */
interface ContravariantInstance
{
    /**
     *  (a' -> a) -> f a -> f a'
     *
     * @template B
     * @param \Closure(B):A $fba
     * @return ContravariantInstance<B>
     */
    public function contramap(\Closure $fba): ContravariantInstance;
}
