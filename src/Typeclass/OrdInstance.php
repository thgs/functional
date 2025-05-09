<?php

namespace thgs\Functional\Typeclass;

use thgs\Functional\Data\Ordering;

/**
 * @template A
 */
interface OrdInstance
{
    /**
     * @param A $b
     */
    public function compare(mixed $b): Ordering;

    /**
     * @param A $b
     */
    public function lessOrEqual(mixed $b): bool;
}
