<?php

namespace thgs\Functional\Instance;

use thgs\Functional\Typeclass\FunctorInstance;
use function thgs\Functional\partial;

/**
 * @template A
 * @template B
 * @template R
 */
class Composition implements FunctorInstance
{
    /** @var callable(R): A */
    private $g;

    public function __construct(callable $g)
    {
        $this->g = $g;
    }

    public function __invoke()
    {
        return partial ($this->g) (...func_get_args());
    }

    /**
     * fmap :: (a -> b) -> (r -> a) -> (r -> b)
     * fmap f g = (\x -> f (g x))
     *
     * Here we need to implement
     * fmap :: (a -> b) -> (r -> b)
     *
     * As the (r -> a) is $this->g
     *
     * @param callable(A): B $f
     * @return Composition
     */
    public function fmap(callable $f): Composition
    {
        // todo: maybe this is too much wrapping in partial?
        $pg = partial ($this->g);
        $pf = partial ($f);

        return new Composition(
            /**
             * @param R $x
             */
            fn ($x) => $pf ($pg($x))
        );
    }
}
