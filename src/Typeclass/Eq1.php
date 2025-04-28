<?php

namespace thgs\Functional\Typeclass;

use thgs\Functional\Container\Method;
use thgs\Functional\Container\MethodContainer;
use thgs\Functional\Container\Type;
use thgs\Functional\Data\Maybe;

class Eq1
{
    private MethodContainer $container;

    public function __construct()
    {
        $this->container = new MethodContainer();
    }

    public static function singleton(): self
    {
        /** @var self|null */
        static $instance;
        if (!$instance) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * @template A
     * @template B
     * @param \Closure(A,B):bool $eq
     * @param A $a
     * @param B $b
     */
    public static function liftEq(\Closure $eq, mixed $a, mixed $b): bool
    {
        if ($a instanceof Eq1Instance) {
            return $a->liftEq($eq, $b);
        }

        /**
         * @var Maybe<bool> $maybe
         */
        $maybe = self::singleton()->container->invoke('liftEq', $a, $eq, $a, $b);
        if (!$maybe->isJust()) {
            throw new \TypeError('Unknown Eq1 instance');
        }

        return $maybe->getValue()->getValue();
    }

    /**
     * @template A
     * @template B
     *
     * @param \Closure(mixed):bool $typePredicate 
     * @param null|\Closure(\Closure(A,B):bool,A,B):bool $liftEq
     */
    public static function register(string $instanceName, \Closure $typePredicate, ?\Closure $liftEq): self
    {
        // deriving methods
        if (!$liftEq) {
            /**
             * We cast to bool so we allow some bad $f that is still castable (for now).
             * Also helps with static analysis but maybe need a second look.
             */
            $liftEq = fn (\Closure $f, $a, $b): bool => (bool) $f($a, $b);
        }

        // register methods
        self::singleton()->container->registerMethod(
            new Method('liftEq', new Type($typePredicate, $instanceName), $liftEq)
        );
        return self::singleton();
    }
}
