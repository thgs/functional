<?php

namespace thgs\Functional\Instance;

use thgs\Functional\Typeclass\FunctorInstance;

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
        // need to decide to curry here?
        return ($this->g)(...func_get_args());
    }

    /**
     * fmap :: (a -> b) -> (r -> a) -> (r -> b)
     * fmap f g = (\x -> f (g x))
     *
     * Here we need to implement
     * fmap :: (a -> b) -> -> (r -> b)
     *
     * As the (r -> a) is $this->g
     *
     * @param callable(A): B $f
     * @return Composition
     */
    public function fmap(callable $f): Composition
    {
        $g = $this->g;
        return new Composition(fn ($x) => $f( $g($x) ));
    }
}