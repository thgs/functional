<?php

namespace thgs\Functional\Data\TypeClass;

use thgs\Functional\Data\Ordering;
use thgs\Functional\Typeclass\EqInstance;

/**
 * @template A of EqInstance
 */
interface Ord
{
    /**
     * @param A $a
     * @param A $b
     */
    public function compare(mixed $a, mixed $b): Ordering;
}
