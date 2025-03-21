<?php

namespace thgs\Functional\Wrapper;

use thgs\Functional\Data\Tuple;
use thgs\Functional\Data\Tuple3;
use thgs\Functional\Typeclass\ContravariantInstance;
use thgs\Functional\Typeclass\FunctorInstance;

use function thgs\Functional\c;
use function thgs\Functional\fmap;
use function thgs\Functional\partial;
use function thgs\Functional\reflectNoOfArguments;

/**
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
 * @todo fix the above issue with the FunctorInstance template.
 */
class Wrapper implements
    ContravariantInstance,
    FunctorInstance
{
    protected function __construct(
        /** @var \Closure(A):mixed */
        private \Closure $wrapped
    ) {}

    /**
     * @param \Closure(A):mixed $a
     * @template B
     * @param null|\Closure(B):A $input
     * @return Wrapper<A>|Wrapper<B>
     */
    public static function withAdjustedInput(\Closure $a, ?\Closure $input = null): self
    {
        $noOfParameters = reflectNoOfArguments($a);

        if ($input) {
            return self::withAdjustedInput($a)->adjustInput($input);
        }

        /** @var Wrapper<A> $instance */
        return match ($noOfParameters) {
            0 => new self($a),
            1 => new self($a),
            2 => new self(fn (Tuple $p) => ($a) ($p->fst(), $p->snd()) ),
            3 => new self(fn (Tuple3 $p) => ($a) ($p->fst3(), $p->snd3(), $p->thd3()) ),
            default => throw new \TypeError('Wrapping with more than 3 arguments is not yet supported'),
        };
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
     * @param \Closure(B):A $fba
     * @return Wrapper<B>
     */
    public function contramap(\Closure $fba): ContravariantInstance
    {
        /** @var Wrapper<B> */
        $new = new self(
            /**
             * @param B $bs
             * @return mixed
             */
            fn ($x) => c ($this->wrapped) ($fba ($x))
        ); 

        return $new;
    }

    /**
     * @template B
     * @param \Closure(B):A $fba
     * @return Wrapper<B>
     */
    public function adjustInput(\Closure $fba): ContravariantInstance
    {
        if (reflectNoOfArguments($fba) > 1) {
            throw new \TypeError("Callable with more than one arguments passed");
        }

        return $this->contramap($fba);
    }

    /**
     * @param A $xs
     * @return mixed
     */
    public function __invoke(...$xs)
    {
        /**
         * Below is like this to support $this->wrapped callables with
         * no args.
         */
        return partial ($this->wrapped, ...$xs);
    }

    /**
     * @template B
     * @param \Closure(A):B $f
     * @return Wrapper<A>
     */
    public function fmap(\Closure $f): FunctorInstance
    {
        return new self(
            fn ($x) => $f (partial ($this->wrapped) ($x))
        );
    }

    /**
     * @template B
     * @param \Closure(A):B $f
     * @return Wrapper<A>
     */
    public function adjustOutput(\Closure $f): FunctorInstance
    {
        if (reflectNoOfArguments($f) > 1) {
            throw new \TypeError("Callable with more than one arguments passed");
        }

        return $this->fmap($f);
    }
}
