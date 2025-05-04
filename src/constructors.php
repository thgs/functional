<?php

namespace thgs\Functional;

use thgs\Functional\Data\Just;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Data\Nothing;
use thgs\Functional\Data\Tuple;
use thgs\Functional\Data\Tuple3;

/**
 * @template X
 * @param X $x
 * @return Maybe<X>
 */
function just(mixed $x): Maybe
{
    return new Maybe(new Just($x));
}

/**
 * @template X
 * @return Maybe<X>
 */
function nothing(): Maybe
{
    // @todo SA has an issue here
    return new Maybe(new Nothing());
}

/**
 * @template A
 * @template B
 * @param A $a
 * @param B $b
 * @return Tuple<A,B>
 */
function t(mixed $a, mixed $b): Tuple
{
    return new Tuple($a, $b);
}

/**
 * @template A
 * @template B
 * @template C
 * @param A $a
 * @param B $b
 * @param C $c
 * @return Tuple3<A,B,C>
 */
function t3(mixed $a, mixed $b, mixed $c): Tuple3
{
    return new Tuple3($a, $b, $c);
}
