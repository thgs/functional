<?php

namespace thgs\Functional\Typeclass\Adapter;

use thgs\Functional\Typeclass\FunctorInstance;
use function thgs\Functional\c;
use function thgs\Functional\partial;

/**
 * @todo No idea how to type hint this correctly, the type variable
 * might not be among the parameters really
 *
 * @template I
 * @implements FunctorInstance<callable(I)>
 *
 * This holds a function as wiring that will perform some action/calculation.
 * Implements a Functor on this wiring fn input
 * So for the example of context logger, the CallableWiringFunctorAdapter
 * implements a functor like FunctorInstance<callable(Tuple):R> where R
 * is the return method of the wrapped function on the psr3Logger.
 * Does this even make sense?
 */
class CallableWiringFunctorAdapter implements FunctorInstance
{
    private \Closure $wiring;

    /**
     * @template I
     * @template O
     * @param callable(I):O $wiring
     *
     * Here we accept a callable that takes arbitrary input and returns inferred output.
     * Arbitrary in the sense that the input is defined on the callback.
     * Inferred in the sense that the output can be inferred from the type of the
     * return call inside the fn.
     *
     * In essense both are arbitrary.
     */
    public function __construct(callable $wiring)
    {
        $this->wiring = $wiring instanceof \Closure ? $wiring : \Closure::fromCallable($wiring);
    }

    /**
     * @template B1
     * @param callable(A):B1 $f
     * @return CallableWiringFunctorAdapter<B1>
     *
     * This has input of a callable that must be callable(I):B and return type B of this
     * callable represents actually the same type as we had from the constructor. If it
     * is different it will break because effectively what we do here in fmap is that
     * we construct a Composition f(g(h(j($input)))) etc to transform the Input before
     * passing it to the wiring fn.
     * There is either something wrong or this is not a functor.
     */
    public function fmap(callable $f): FunctorInstance
    {
        return new self( c ($f) ->fmap ($this->wiring) );
    }

    public function __invoke()
    {
        return partial ($this->wiring) (...func_get_args());
    }
}
