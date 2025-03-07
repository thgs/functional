<?php

namespace thgs\Functional\Instance;

use thgs\Functional\Typeclass\FunctorInstance;
use function thgs\Functional\partial;

/**
 * @template R
 * @template A
 * @template B
 *
 * @implements FunctorInstance<A>
 */
class Composition implements FunctorInstance
{
    /** @var callable(R): A */
    private $g;

    /**
     * @param callable(R):A $g
     */
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
     * @template B1
     * @param callable(A):B1 $f
     * @return Composition<R,B1,A>
     */
    public function fmap(callable $f): Composition
    {
        // todo: is $x really needed?

        return new Composition(
            /**
             * @param R $x
             * @return B1
             */
            fn ($x) => partial ($f) /*$*/ (partial ($this->g) ($x))
        );
    }

    public function getReflectionFunction(): \ReflectionFunction
    {
        return new \ReflectionFunction(
            $this->g instanceof \Closure ? $this->g : \Closure::fromCallable($this->g)
        );
    }

    /**
     * @return callable(R):A
     */
    public function getInnerCallable(): callable
    {
        return self::unwrap($this);
    }

    /**
     * @template R1
     * @template A1
     * @template B1
     * @param Composition<R1,A1,B1> $composition
     * @return callable(R1):A1
     */
    public static function unwrap(Composition $composition): callable
    {
        return $composition->g;
    }
}
