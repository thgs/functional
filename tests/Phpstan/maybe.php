<?php

use thgs\Functional\Data\Just;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Data\Nothing;
use function PHPStan\Testing\assertType;
use function thgs\Functional\just;
use function thgs\Functional\nothing;

/**
 * -- Both constructions below (1,2) cannot possibly resolve string
 * and that is fine.
 *
 * Therefore the constructor has been made protected.
 */

// Construction with objects (1)
//assertType('thgs\Functional\Data\Maybe<string>', new Maybe(new Just('abc'))); // fine
//assertType('thgs\Functional\Data\Maybe<mixed>',  new Maybe(new Nothing()));   // fine

// Construction with functions (2)
//assertType('thgs\Functional\Data\Maybe<string>', just('abc')); // fine
//assertType('thgs\Functional\Data\Maybe<never>',  nothing());   // fine


/**
 * -- Assignment to a variable
 */

$maybe = Maybe::just('abc');
assertType('thgs\Functional\Data\Maybe<string>', $maybe);

/**
 * Nothing without annotation (X=never)
 */
$maybeNothing = Maybe::nothing();
assertType('thgs\Functional\Data\Maybe<never>', $maybeNothing);

/**
 * Required to annotate, which is fine.
 *
 * @var Maybe<string>
 *
 * Note, keep annotated variable to a different name otherwise it will
 * interfere at Point A (see below).
 */
$mAnnotated = Maybe::nothing();
assertType('thgs\Functional\Data\Maybe<string>', $mAnnotated);


/**
 * Mapping from nullable types category, within the context of
 * a function.  Note that both return a `Maybe<string>`, which is
 * exactly the goal.
 */


/**
 * @return Maybe<string>
 */
function fromNullable(?string $x): Maybe
{
    if ($x !== null) {
        return Maybe::just($x);
    }

    return Maybe::nothing();
}

/**
 * @return Maybe<string>
 */
function fromNullableWithFunctions(?string $x): Maybe
{
    if ($x !== null) {
        return just($x);
    }

    return nothing();
}

assertType('thgs\Functional\Data\Maybe<string>', fromNullable('abc')); // fine
assertType('thgs\Functional\Data\Maybe<string>', fromNullable(null));  // fine

assertType('thgs\Functional\Data\Maybe<string>', fromNullableWithFunctions('abc')); // fine
assertType('thgs\Functional\Data\Maybe<string>', fromNullableWithFunctions(null));  // fine


/**
 * -- Getting the inner value
 *
 * Here we cannot resolve without an if-statement first (fine).
 */

$maybe = fromNullable('abc');
assertType('thgs\Functional\Data\Just<string>|thgs\Functional\Data\Nothing', $maybe->getValue());

$maybe = fromNullable(null);
assertType('thgs\Functional\Data\Just<string>|thgs\Functional\Data\Nothing', $maybe->getValue());


$maybe = fromNullable('abc');
if ($maybe->isJust()) {
    assertType('thgs\Functional\Data\Just<string>', $maybe->getValue());
} else {
    assertType('thgs\Functional\Data\Nothing', $maybe->getValue());
}

/**
 * -- Unwrapping
 *
 * Here again we cannot resolve without an if-statement first (fine).
 */

$maybe = fromNullable('abc');
assertType('string|null', $maybe->unwrap());

$maybe = fromNullable(null);
assertType('string|null', $maybe->unwrap());

// Point A for reference.
$maybe = fromNullable('abc');
if ($maybe->isJust()) {
    assertType('string', $maybe->unwrap());
} else {
    assertType('null', $maybe->unwrap());
}


/**
 * -- Consuming a value
 */

/**
 * @template X
 * @param Maybe<X> $maybeValue
 */
function consumerForMaybe(Maybe $maybeValue): void
{
    // This is a little strange type, as `Maybe<X>` fails here.
    assertType('thgs\Functional\Data\Maybe<X (function consumerForMaybe(), argument)>', $maybeValue);

    // This is a little strange type
    assertType('X (function consumerForMaybe(), argument)|null', $maybeValue->unwrap());
}

/**
 * @template X of string
 * @param Maybe<X> $maybeValue
 */
function consumerConstrainedForMaybe(Maybe $maybeValue): string
{
    // This is a little strange type, as `Maybe<X>` fails here.
    assertType('thgs\Functional\Data\Maybe<X of string (function consumerConstrainedForMaybe(), argument)>', $maybeValue);

    // This is a little strange type
    assertType('X of string (function consumerConstrainedForMaybe(), argument)|null', $maybeValue->unwrap());

    return $maybeValue->isJust() ? $maybeValue->unwrap() : 'Default string';
}

$return = consumerConstrainedForMaybe(fromNullable('abc'));
assertType('string', $return);

$return = consumerConstrainedForMaybe(fromNullable(null));
assertType('string', $return);

