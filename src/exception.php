<?php

namespace thgs\Functional;


use thgs\Functional\Data\Just;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Data\Nothing;


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
        $value = new Maybe(new Just($f(...$xs)));
    } catch (\Throwable $e) {
        /**
         * Type hint because of Nothing
         * @var Maybe<R> $value
         */
        $value = new Maybe(new Nothing());
    }
    return $value;
}

