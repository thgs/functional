<?php

namespace thgs\Functional\Typeclass;

/**
 * @template A of EqInstance
 */
interface EqInstance
{
    /**
     * @param A $other
     */
    public function equals(EqInstance $other): bool;

    /**
     * @param A $other
     */
    public function notEquals(EqInstance $other): bool;
}
