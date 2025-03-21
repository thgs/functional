<?php

namespace thgs\Functional\Instance;

use thgs\Functional\Typeclass\MonoidInstance;


/**
 * @implements MonoidInstance<int>
 */
class IntegerAddition implements
    MonoidInstance
{
    public static function mempty(): int
    {
        return 0;
    }

    public static function mappend(): \Closure
    {
        return fn (int $a, int $b): int => $a + $b;
    }
}
