<?php

namespace thgs\Functional;

use thgs\Functional\Data\Either;
use thgs\Functional\Data\Just;
use thgs\Functional\Data\Left;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Data\Nothing;
use thgs\Functional\Data\Right;
use thgs\Functional\Typeclass\Attribute\FunctorInstance as FunctorInstanceAttribute;
use thgs\Functional\Typeclass\FunctorInstance;
use thgs\Functional\Typeclass\ShowInstance;

/**
 * @template A
 * @template B
 *
 * @param callable $f
 * @psalm-param pure-callable(A): B $f
 *
 * @param FunctorInstance<A>|object|callable $g
 * @return FunctorInstance<B>|object|callable
 */
function fmap(callable $f, object|callable $g): object|callable {
    if ($g instanceof FunctorInstance) {
        // call the instance method
        // todo: since $f is callable, we can wrap it in Composition?
        return $g->fmap($f);
    }

//    if (!is_object($g)) {
//        // temporarily do not support functions, if that is even possible or make sense (can a function be a typeclass itself?)
//        // read this: https://stackoverflow.com/questions/43379364/typeclass-instances-for-functions
//        throw new \TypeError('Functions are not yet supported as a functor value in fmap. Sorry.');
//    }

    $fmapMethod = getAttributeProperty($g, FunctorInstanceAttribute::class, 'fmap');

    return $g->$fmapMethod($f);

    // todo: support looking through methods if getAttributes on the reflObject is not doing it
}

// todo: move to internal
// todo: change from object to ReflectionObject|ReflectionFunction
function getAttributeProperty(object $value, string $attribute, string $property): ?string
{
    $reflectionObject = new \ReflectionObject($value);

    $functorAttributes = $reflectionObject->getAttributes($attribute);
    if (empty($functorAttributes)) {
        throw new \Exception('I dont know how to fmap this value!');
    }

    if (count($functorAttributes) > 1) {
        throw new \InvalidArgumentException('Multiple Functor attributes not supported yet. Sorry!!');
    }

    $functorMetadata = array_shift($functorAttributes);
    [$property => $targetValue] = $functorMetadata->getArguments();
    return $targetValue;
}

function show(int|string|float|bool|ShowInstance $x): string
{
    return (string) $x;
}

/**
 * @template A
 * @template B
 * @template C
 *
 * @param callable(A): C $f
 * @param callable(B): C $g
 * @param Either $either
 * @return C
 */
function either(callable $f, callable $g, Either $either)
{
    $value = $either->getValue();
    return match (\true) {
        $value instanceof Left => $f($value->getValue()),
        $value instanceof Right => $g($value->getValue())
    };
}

/**
 * @template A
 * @template B
 * @param B $default
 * @param callable(A): B $f
 * @param Maybe $maybe
 * @return B
 */
function maybe($default, callable $f, Maybe $maybe)
{
    $value = $maybe->getValue();
    return match (\true) {
        $value instanceof Nothing => $default,
        $value instanceof Just => $f($value->getValue())
    };
}