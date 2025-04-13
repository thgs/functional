<?php

namespace thgs\Functional\Typeclass;

/**
 * @template A
 */
interface EqInstance
{
    /**
     * @param EqInstance<A> $other
     */
    public function equals(EqInstance $other): bool;

    /**
     * @param EqInstance<A> $other
     */
    public function notEquals(EqInstance $other): bool;
}
