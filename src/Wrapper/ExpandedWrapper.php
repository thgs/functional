<?php

namespace thgs\Functional\Wrapper;

use thgs\Functional\Data\Tuple;
use thgs\Functional\Data\Tuple3;
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
class ExpandedWrapper implements
    ContravariantInstance,
    FunctorInstance
{
    public function __construct(
        /** @var \Closure (A):mixed */
        private \Closure $wrapped
    ) {}

    /**
     * This is implemented like this because we cannot use $this in static context.
     */
    private function constructWithTuple2(): self
    {
        return new self(fn (Tuple $p) => ($this) ($p->fst(), $p->snd()) );
        //return $this->contramap(fn (Tuple $p) => partial ($this->wrapped, $p->fst(), $p->snd()));
    }

    /**
     * This is implemented like this because we cannot use $this in static context.
     */
    private function constructWithTuple3(): self
    {
        return new self(fn (Tuple3 $p) => ($this) ($p->fst3(), $p->snd3(), $p->thd3()) );
        //return $this->contramap(fn (Tuple3 $p) => c ($this->wrapped) ($p->fst3(), $p->snd3(), $p->thd3()));
    }

    /**
     * @param \Closure(A):mixed $a
     * @template B
     * @param null|\Closure(B):A $input
     * @return Wrapper<A>|Wrapper<B>
     */
    public static function withAdjustedInput(\Closure $a, ?\Closure $input = null): self
    {
        $instance = new self($a);
        if (!$input) {
            // todo: is the reflection really worth it? User should know already.
            $reflection = new \ReflectionFunction(\Closure::fromCallable($a));
            $noOfParameters = $reflection->getNumberOfParameters();

            /** @var Wrapper<A> $instance */
            return match ($noOfParameters) {
                0 => $instance,
                1 => $instance,
                2 => $instance->constructWithTuple2(),
                3 => $instance->constructWithTuple3(),
                default => throw new \Exception('Wrapping with more than 3 arguments is not yet supported'),
            };
        }
        /** @var Wrapper<B> $newInstance*/
        $newInstance = $instance->contramap($input);
        return $newInstance;
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
            fn (...$bs) => c ($this->wrapped) ($fba (...$bs))
        ); 

        return $new;
    }

    public function adjustInput(\Closure $fba): ContravariantInstance
    {
        return $this->contramap($fba);
    }

    /**
     * Implementing __invoke is required so we can actually call the
     * wrapped callable, but we offer partial application as well.
     *
     * @param A $xs
     * @return mixed
     */
    public function __invoke(...$xs)
    {
        // buggy: return partial ($this->wrapped) (...func_get_args());
        return partial ($this->wrapped, ...$xs);
    }

    /**
     * fmap :: (a -> b) -> Wrapper a -> Wrapper b
     *
     * Template letters are inversed below to follow what
     * we initially defined for Contravariant. The end result
     * is not different input but different output
     */
    public function fmap(\Closure $f): FunctorInstance
    {
        return new self(
            fn (...$xs) => $f (partial ($this->wrapped, ...$xs))
        );

        // the below would still be valid but more expensive
        // todo: it is not valid.
        return new self(
            fn ($x) => fmap ($f, c ($this->wrapped, $x))
        );
    }

    public function adjustOutput(\Closure $f): FunctorInstance
    {
        return $this->fmap($f);
    }
}
