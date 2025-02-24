<?php

namespace thgs\Functional\Data;

use thgs\Functional\Instance\Composition;
use thgs\Functional\Typeclass\FunctorInstance;


/**
 * @template R
 * @implements FunctorInstance<\Closure():R>
 *
 * Here we care more for the result of that action, rather than the action itself.
 * So for now the template is the result.
 * @todo phpstan complains a lot.
 */
class IO implements FunctorInstance
{
    /**
     * @var \Closure():R
     */
    private \Closure $action;

    /**
     * @param callable():R $action
     */
    public function __construct(callable $action)
    {
        $this->action = \Closure::fromCallable($action);
    }

    /**
     * @return R
     */
    public function getValue()
    {
        return ($this->action)();
    }

    /*
     * todo: For the annotation, older code uses (see Maybe::fmap):
     *       return FunctorInstance<B1>
     * which one is better? IO<B1> or FunctorInstance<B1>
     * Should be IO since after they fmap there may be other things they do.
     */

    /**
     * @template B1
     * @param callable(R):B1
     * @return FunctorInstance<B1>
     */
    public function fmap(callable $f): FunctorInstance
    {
        /**
         * This should not run the action.
         */

        /**
         * @todo How could use a Composition ?
         * Wip with Composition
         * var Composition<R,B1,IO<B1>>
        $composition = new Composition( fn () => ($this->action)() );
        $composition->fmap($f);
        return new IO($composition);
         */

        // todo: self or static?
        return new self(
            function () use ($f) {
                $result = ($this->action)();
                return $f($result);
            }
        );
    }
}
