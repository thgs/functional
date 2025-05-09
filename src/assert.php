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


/**
 * Eq laws
 */


function assertEqIsReflective(mixed $a): bool
{
    return equals($a, $a);
}


function assertEqIsSymmetric(mixed $x, mixed $y): bool
{
    return equals($x, $y) == equals($y, $x);
}


function assertEqIsTransitive(mixed $x, mixed $y, mixed $z): bool
{
    if (!equals($x, $y)) {
        throw new \Exception('x and y must be equal to assert transitivity');
    }
    if (!equals($y, $z)) {
        throw new \Exception('y and z must be equal to assert transitivity');
    }
    return equals($x, $z);
}


/**
 * @template A
 * @template B
 * @param A $x
 * @param B $y
 * @param \Closure(A|B):mixed $f Return type should implement Eq/EqInstance
 */
function assertEqIsExtentable(mixed $x, mixed $y, \Closure $f): bool
{
    if (!equals($x, $y)) {
        throw new \Exception('x and y must be equal to assert extensionality');
    }
    return equals($f($x), $f($y));
}


function assertEqCanNegate(mixed $x, mixed $y): bool
{
    return notEquals($x, $y) == !equals($x, $y);
}
