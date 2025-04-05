<?php

namespace thgs\Functional\Typeclass;

/**
 * @template A
 * @template C
 */
interface ProfunctorInstance
{
    /**
     * @template B
     * @template D
     * @param \Closure(B):A $f
     * @param \Closure(C):D $g
     * @return ProfunctorInstance<B,D>
     */
    public function dimap(\Closure $f, \Closure $g): ProfunctorInstance;
}
