<?php

namespace thgs\Functional\Typeclass\Adapter;

use thgs\Functional\Typeclass\FunctorInstance;

/**
 * @template A1
 * @implements  FunctorInstance<A1>
 *
 */
class FunctorAdapter implements FunctorInstance
{
    public function __construct(
        private object $wrap,
        private string $method = '__invoke'
    ) {
        if (!method_exists($wrap, $method)) {
            throw new \InvalidArgumentException('Method does not exist in given object.');
        }

        if (!is_callable([$wrap, $method])) {
            throw new \TypeError('Combination not callable');
        }

        // assuming here the $method is of type method(callable): self
    }

    /**
     * @template B
     * @return FunctorInstance<B>
     */
    public function fmap(\Closure $f): FunctorInstance
    {
        // the redundant type check is missing here, adapter relies completely on static analysis.

        // todo: every call to fmap will apply, not lazy.
        // to make it lazy we will need to store the calls and implement some sort of proxy for __call
        // that will call them then (for example), or explicitly client will say "now evaluate" but in
        // both cases we need to keep track of the whole composition

        /*
         * todo: psalm cant figure out type of $newData - fair enough
         *      as a result complains because we pass mixed instead of object in new FunctorAdapter($newData..)
         * todo: psalm cant figure out type of $this->wrap - its an object.. we have checked that the method exists.
         */
        $newData = $this->wrap->{$this->method}($f);

        return new FunctorAdapter($newData, $this->method);
    }

    /**
     * Bit of a proxy here
     * todo: phpstan does not like it as is.
     */
    public function __call(string $name, array $arguments)
    {
        if (!method_exists($this->wrap, $name)) {
            throw new \InvalidArgumentException('Method does not exist in given object.');
        }

        return $this->wrap->$name(...$arguments);
    }

    // todo: add a __callStatic here too ?
}
