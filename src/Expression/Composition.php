<?php

namespace thgs\Functional\Expression;

use thgs\Functional\Typeclass\ContravariantInstance;
use thgs\Functional\Typeclass\FunctorInstance;
use function thgs\Functional\partial;

/**
 * @template R
 * @template A
 *
 * @implements FunctorInstance<A>
 * @implements ContravariantInstance<A>
 *
 * @todo not sure if you can implement both covariant and
 * contravariant functor instances with the same A in the same object
 * and still make sense, sure we could just use one or the other and
 * share a PHP class for now.
 * @see https://hackage.haskell.org/package/base-4.21.0.0/docs/Data-Functor-Contravariant.html#v:phantom
 *
 * This is not just function composition but also a wrapper around
 * `partial`.  In times you may opt in to use just `partial` but if
 * you wish for the full functionality you may opt in to use
 * Composition.
 *
 * Function composition is provided by implementing Functor ((r ->)),
 * see fmap.
 *
 * @todo this only supports single argument functions (for the type hinting)
 */
class Composition implements
    FunctorInstance,
    ContravariantInstance
{
    /**
     * Use `c` if you want to create a Composition for a callable
     */
    public function __construct(
        /** @var \Closure(R):A $g */
        private \Closure $g
    ) {}

    /**
     * @return A
     */
    public function __invoke(): mixed
    {
        return partial ($this->g, ...func_get_args());
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
     * @template B
     * @param \Closure(A):B $f
     * @return Composition<R,B>
     */
    public function fmap(\Closure $f): Composition
    {
        // todo: is $x really needed?
        // todo: last-resort type-hint below
        /** @var Composition<R,B> */
        $r = new Composition(
            /**
             * @param R $x
             * @return B
             */
            fn ($x) => partial ($f, /*$*/
                                partial ($this->g, $x))
        );
        return $r;
    }

    /**
     * contramap' :: (b -> a) -> (a -> r) -> (b -> r)
     * flipped a with r to align with this class
     * contramap' :: (b -> r) -> (r -> a) -> (b -> a)
     *
     * Essentially, let $this = f b then
     * (>$$<) :: Contravariant f => f b -> (a -> b) -> f a
     * re-written to make sense with type variables here
     * let $this = f a
     * (>$$<) :: Contravariant f => f a -> (b -> a) -> f b
     *
     * @template B
     * @param \Closure(B):R $fba
     * @return Composition<B,A>
     */
    public function contramap(\Closure $fba): ContravariantInstance
    {
        // manually flipped, the definition is really `contramap = flip (.)`
        // todo: last-resort type-hint below
        /** @var Composition<B,A> */
        $r = new Composition(
            /**
             * @param R $x
             * @return B
             */
            fn ($x) => partial ($this->g, /*$*/
                                partial ($fba, $x))
        );
        return $r;
    }

    public function getReflectionFunction(): \ReflectionFunction
    {
        return new \ReflectionFunction($this->g);
    }

    /**
     * @return \Closure(R):A
     */
    public function getInnerCallable(): \Closure
    {
        return $this->g;
    }

    /**
     * @template R1
     * @template A1
     * @param Composition<R1,A1> $composition
     * @return \Closure(R1):A1
     */
    public static function unwrap(Composition $composition): \Closure
    {
        return $composition->g;
    }
}
