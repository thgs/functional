<?php

namespace thgs\Functional\Data;

use thgs\Functional\Typeclass\ApplicativeInstance;
use thgs\Functional\Typeclass\FunctorInstance;
use thgs\Functional\Typeclass\MonadInstance;

use function thgs\Functional\partial;


/**
 * @template ReturnType
 *
 * Here we care more for the result of that action, rather than the action itself.
 * So for now the template is the result.
 * @todo phpstan complains a lot.
 *
 * @implements FunctorInstance<ReturnType>
 * @implements ApplicativeInstance<ReturnType>
 * @implements MonadInstance<ReturnType>
 */
class IO implements
    FunctorInstance,
    ApplicativeInstance,
    MonadInstance
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
     * This is effectively (<-)
     * @return R
     */
    public function getValue()
    {
        return ($this->action)();
    }

    /**
     * @return R
     */
    public function __invoke()
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

    public static function pure($a): ApplicativeInstance
    {
        // constructor will throw typeError if it is not callable.
        return new IO($a);
    }

    /**
     * (<*>) :: f (a -> b) -> f a -> f b
     * <*> is always implemented in reverse with what the others are
     * so this instance is actually the `f ( a -> b)` instead of `f a`.
     * Is that correct/usable? How you sequence more than one?
     */
    public function sequence(ApplicativeInstance $fa): ApplicativeInstance
    {
        // runtime type-check
        if (!$fa instanceof IO) {
            throw new \TypeError('Expected instance of IO');
        }

        // todo: rewrite this in a more concise manner, but for brevity now:
        $do = function () use ($fa) {
            $f = $this();
            $g = $fa();
            return partial ($f) ($g);
        };
        return IO::inject($do);
    }

    /**
     * @template R
     * @param R $a
     * @return IO<R>
     */
    public static function inject($a): MonadInstance
    {
        return is_callable($a) ? new self($a) : new self(fn () => $a);
    }

    public function bind(callable $f): MonadInstance
    {
        throw new \Exception('Will be implemented');
    }
}
