<?php

namespace thgs\Functional;

/**
 * @todo phpstan does not seem to like when template arguments are used here
 * @param \Closure(\Closure,\Closure):\Closure $compositionFunction
 * @param \Closure $f
 */
function assertCompositionRespectsIdentity(\Closure $compositionFunction, \Closure $f, mixed $a): bool
{
    $left = $compositionFunction  (id(...), $f);
    $right = $compositionFunction ($f, id(...));
    return $left($a) == $right($a);
}

/**
 * This is highly unreliable, might need to see into it later.
 */
function assertIsTotalFunction(\Closure $f): bool
{
    $returnType = reflectReturnType($f);
    /**
     * never can only be used as a standalone type, and if there is no
     * return type, we cannot say.
     */
    return !empty($returnType) && $returnType !== 'never';
}
