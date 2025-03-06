<?php

namespace thgs\Functional\Typeclass\Adapter;

use thgs\Functional\Typeclass\FunctorInstance;
use function thgs\Functional\c;
use function thgs\Functional\partial;

/**
 * @todo No idea how to type hint this correctly, the type variable
 * might not be among the parameters really
 */
class CallableWiringFunctorAdapter implements FunctorInstance
{
    private \Closure $wiring;

    public function __construct(callable $wiring)
    {
        $this->wiring = $wiring instanceof \Closure ? $wiring : \Closure::fromCallable($wiring);
    }

    public function fmap(callable $f): FunctorInstance
    {
        return new self( c ($f) ->fmap ($this->wiring) );
    }

    public function __invoke()
    {
        return partial ($this->wiring) (...func_get_args());
    }
}
