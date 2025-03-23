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
        return new Maybe(new Just($f(...$xs)));
    } catch (\Throwable $e) {
        return new Maybe(new Nothing());
    }
}

