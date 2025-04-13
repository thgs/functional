<?php

namespace thgs\Functional;

function reflectNoOfArguments(callable $f): int
{
    return (new \ReflectionFunction(
        $f instanceof \Closure ? $f : \Closure::fromCallable($f)))
        ->getNumberOfParameters();
}

function reflectReturnType(callable $f): string
{
    return (string) (new \ReflectionFunction(
        $f instanceof \Closure ? $f : \Closure::fromCallable($f)))
        ->getReturnType();
}
