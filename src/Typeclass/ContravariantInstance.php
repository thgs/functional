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
     * @param callable(B):A $fba
     * @return ContravariantInstance<B>
     */
    public function contramap(callable $fba): ContravariantInstance;
}
