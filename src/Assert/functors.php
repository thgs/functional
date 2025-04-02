<?php

namespace thgs\Functional\Assert;

use thgs\Functional\Typeclass\FunctorInstance;
use function thgs\Functional\fmap; 

/**
 * @todo Add tests for these
 */

/**
 * @template A1
 * @template X
 * @template Y
 * @param FunctorInstance<A1> $subject
 * @param \Closure(X):Y $f
 * @param \Closure(A1):X $g
 */
function assertInstanceIsFunctor(
    FunctorInstance $subject,
    \Closure $f,
    \Closure $g
): ?string {
    return assertFunctorInstanceMapsId($subject)
        ?? assertFunctorInstanceIsAssociative($subject, $f, $g);
}

/**
 * @template A1
 * @param FunctorInstance<A1> $subject
 * @todo is this enough to prove associativity?
 */
function assertFunctorInstanceMapsId(FunctorInstance $subject): ?string
{
    $result = $subject->fmap(fn ($x) => $x);

    $class = get_class($subject);
    if (!$result instanceof $class) {
        return 'result is not of the same class';
    }

    // todo: redefine equality here. It is expected not to be the same object
    // or have the same internal structure in most cases of a functor.
    if ($subject != $result) {
        return 'result is not the same after fmap';
    }

    return null;
}

/**
 * @template A1
 * @template X
 * @template Y
 * @param FunctorInstance<A1> $subject
 * @param \Closure(X):Y $f
 * @param \Closure(A1):X $g
 * @todo is this enough to proove associativity?
 */
function assertFunctorInstanceIsAssociative(
    FunctorInstance $subject,
    callable $f,
    callable $g
): ?string {
    $composition = fn ($x) => $f($g($x));

    if (fmap($composition, $subject) != fmap($f, fmap($g, $subject))) {
        return 'instance is not associative';
    }
    /*
    if ($subject->fmap($composition) != $subject->fmap($g)->fmap($f)) {
        return 'instance is not associative';
    }
    */

    return null;
}
