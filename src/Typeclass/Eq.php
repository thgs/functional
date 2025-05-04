<?php

namespace thgs\Functional\Typeclass;

use thgs\Functional\Container\Method;
use thgs\Functional\Container\MethodContainer;
use thgs\Functional\Container\Type;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Typeclass\EqInstance;

class Eq
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
     * @template A1
     * @template B1
     * @param A1 $a
     * @param B1 $b
     */
    public static function equals(mixed $a, mixed $b): bool
    {
        /**
         * This does not really need to check if $b is also EqInstance.
         */
        if ($a instanceof EqInstance && $b instanceof EqInstance) {
            return $a->equals($b);
        }

        /**
         * @var Maybe<bool> $maybe
         */
        $maybe = self::singleton()->container->invoke('equals', $a, $a, $b);
        if (!$maybe->isJust()) {
            /**
             * Allow method container to override but default to use
             * the `==` operator of PHP.  Is this better than loading
             * the container with derived functions always?
             */
            return $a == $b;
        }

        return $maybe->getValue()->getValue();
    }

    /**
     * @template A1
     * @template B1
     * @param A1 $a
     * @param B1 $b
     */
    public static function notEquals(mixed $a, mixed $b): bool
    {
        /**
         * This does not really need to check if $b is also EqInstance.
         */
        if ($a instanceof EqInstance && $b instanceof EqInstance) {
            return $a->notEquals($b);
        }

        /**
         * @var Maybe<bool> $maybe
         */
        $maybe = self::singleton()->container->invoke('notEquals', $a, $a, $b);
        if (!$maybe->isJust()) {
            /**
             * Allow method container to override but default to use
             * the `!=` operator of PHP.  Is this better than loading
             * the container with derived functions always?
             */
            return $a != $b;
        }

        return $maybe->getValue()->getValue();
    }

    /**
     * Usage:
     *
     * Functor::register(equals: fn ($a, $b) => $a == $b, typePredicate: is_int(...), instanceName: 'int')
     *
     * @template A
     * @template B
     *
     * @param \Closure(mixed):bool $typePredicate 
     * @param null|\Closure(A,B):bool $equals
     * @param null|\Closure(A,B):bool $notEquals
     */
    public static function register(string $instanceName, \Closure $typePredicate, ?\Closure $equals, ?\Closure $notEquals): self
    {
        // deriving methods
        if ($equals === null && $notEquals !== null) {
            $equals = fn ($a, $b) => !$notEquals($a, $b);
        }
        if ($notEquals === null && $equals !== null) {
            $notEquals = fn ($a, $b) => !$equals($a, $b);
        }

        if ($notEquals === null || $equals === null) {
            throw new \Exception('Must provide at least one of the two implementations (equals,notEquals)');
        }

        // register methods
        self::singleton()->container->registerMethod(
            new Method('equals', new Type($typePredicate, $instanceName), $equals)
        );
        self::singleton()->container->registerMethod(
            new Method('notEquals', new Type($typePredicate, $instanceName), $notEquals)
        );
        return self::singleton();
    }
}
