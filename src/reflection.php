<?php

namespace thgs\Functional;

function reflectNoOfArguments(callable $f): int
{
    return (new \ReflectionFunction(
        $f instanceof \Closure ? $f : \Closure::fromCallable($f)))
        ->getNumberOfParameters();
}
