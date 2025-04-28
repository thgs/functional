<?php

namespace thgs\Functional\Typeclass;

/**
 * @template A
 */
interface Eq1Instance
{
    /**
     * @template B
     * @param \Closure(A,B):bool $eq
     * @param B $other
     *
     * @todo is $other mixed or Eq1Instance ? or can we somehow
     * indicate it is "same class" as implementation?
     */
    public function liftEq(\Closure $eq, mixed $other): bool;
}
