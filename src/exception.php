<?php

namespace thgs\Functional;

use thgs\Functional\Data\Maybe;


/**
 * @todo this can probably expand into a type class?
 *
 * @template R
 * @param \Closure():R $f
 * @return Maybe<R>
 */
function safe(\Closure $f, mixed ...$xs): Maybe
{
    try {
        $value = Maybe::just($f(...$xs));
    } catch (\Throwable $e) {
        $value = Maybe::nothing();
    }
    return $value;
}

