<?php

namespace thgs\Functional\Instance;

use thgs\Functional\Typeclass\MonoidInstance;

/**
 * @implements MonoidInstance<IntegerAddition, int>
 */
class IntegerAddition implements
    MonoidInstance
{
    public function mempty(): int
    {
        return 0;
    }

    public function mappend(mixed $a, mixed $b): int
    {
        return $a + $b;
    }
}
