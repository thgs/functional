<?php

namespace thgs\Functional\Wrapper;

use thgs\Functional\Typeclass\ContravariantInstance;
use thgs\Functional\Typeclass\FunctorInstance;
use function thgs\Functional\c;
use function thgs\Functional\fmap;
use function thgs\Functional\partial;


/**
 * data Wrapper g = Callback g
 * 
 * In a callback we could have any object with the information on what
 * method to call.
 * 
 * eg. fn ($x, $y, $z) => $psr3Logger->critical($x, [$y, $z]);
 *
 * We generally do not care much about the output of the callback for
 * the Wrapper. The wrapper cares about the input. So template A is
 * describing the input. Essentially, Wrapper<A> means a wrapper of
 * something `unknown` that receives input of type A. Unknown in the
 * sense that the output of it could be anything.
 * 
 * @template A
 *
 * To be able to adjust the input, Wrapper is an instance of
 * Contravariant like so:
 *
 * instance Contravariant (Wrapper a) where
 *     contramap :: (b -> a) -> Wrapper a -> Wrapper b
 * 
 * @implements ContravariantInstance<A>
 *
 * After we contramap and adjust the input, we may want to adjust the
 * output as well. So we can implement Functor (Wrapper a) to do so.
 * The Functor instance and the __invoke call mandate defining a template
 * parameter so we can define the output in their instances, the below
 * is not correct for example if one reads through the definitions in
 * FunctorInstance.
 *
 * @implements FunctorInstance<A>
 */
class Wrapper implements
    ContravariantInstance,
    FunctorInstance
{
    /** @var callable(A):mixed */
    private $wrapped;
    
    /**
     * @param callable(A):mixed $a
     */
    public function __construct(callable $a)
    {
        $this->wrapped = $a;
    }

    /**
     * contramap :: (b -> a) -> Wrapper a -> Wrapper b
     *
     * Wrapper a is $this therefore here we need to return Wrapper
     * b. Essentially we need to return something that will use the (b
     * -> a) to plug into the Wrapper a but accept input from type b.
     *
     * Use this method to adjust the input.
     *
     * @template B
     * @param callable(B):A $fba
     * @return Wrapper<B>
     */
    public function contramap(callable $fba): ContravariantInstance
    {
        /** @var Wrapper<B> */
        $new = new self(
            /**
             * @param B $b
             * @return mixed
             */
            fn ($b) => c ($this->wrapped) ($fba ($b))
        ); 

        return $new;
    }

    /**
     * Implementing __invoke is required so we can actually call the
     * wrapped callable, but we offer partial application as well.
     *
     * @return mixed
     */
    public function __invoke()
    {
        return partial ($this->wrapped) (...func_get_args());
    }

    /**
     * fmap :: (a -> b) -> Wrapper a -> Wrapper b
     *
     * Template letters are inversed below to follow what
     * we initially defined for Contravariant. The end result
     * is not different input but different output
     */
    public function fmap(callable $f): FunctorInstance
    {
        return new self(
            fn ($x) => $f (partial ($this->wrapped) ($x))
        );

        // the below would still be valid but more expensive
        return new self(
            fn ($x) => fmap ($f, c ($this->wrapped))
        );
    }
}
