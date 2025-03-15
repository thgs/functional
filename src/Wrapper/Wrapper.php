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
     * @param callable(A):mixed $a
     * @template B
     * @param null|callable(B):A $input
     * @return Wrapper<A>|Wrapper<B>
     */
    public static function withAdjustedInput(callable $a, ?callable $input = null): self
    {
        $noOfParameters = reflectNoOfArguments($a);

        if ($input) {
            $inputNoOfParameters = reflectNoOfArguments($input);
            if ($inputNoOfParameters > 1) {
                throw new \TypeError('Given input function has more parameters than 1.');
            }
            return (new self($a))->contramap($input);
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
     * @param callable(B):A $fba
     * @return Wrapper<B>
     */
    public function contramap(callable $fba): ContravariantInstance
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

    public function adjustInput(callable $fba): ContravariantInstance
    {
        // todo: is this tedious type check?
        if (reflectNoOfArguments($fba) > 1) {
            throw new \TypeError("Callable with more than one arguments passed");
        }

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
        return partial ($this->wrapped) (...$xs);
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
        // todo: it is not valid.
        return new self(
            fn ($x) => fmap ($f, c ($this->wrapped, $x))
        );
    }

    public function adjustOutput(callable $f): FunctorInstance
    {
        // todo: is this tedious type check?
        if (reflectNoOfArguments($f) > 1) {
            throw new \TypeError("Callable with more than one arguments passed");
        }

        return $this->fmap($f);
    }
}
