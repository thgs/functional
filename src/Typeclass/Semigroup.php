<?php

namespace thgs\Functional\Typeclass;

use thgs\Functional\Container\Method;
use thgs\Functional\Container\MethodContainer;
use thgs\Functional\Container\Type;
use thgs\Functional\Data\Maybe;

class Semigroup
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
     * @param A $a
     * @param A $b
     * @return A
     */
    public static function assoc(mixed $a, mixed $b): mixed
    {
        if ($a instanceof SemigroupInstance) {
            return $a->assoc($b);
        }

        /**
         * @var Maybe<A>
         */
        $maybe = self::singleton()->container->invoke('assoc', $a, $a, $b);
        if (!$maybe->isJust()) {
            throw new \TypeError('Unknown Semigroup instance');
        }

        return $maybe->getValue()->getValue();
    }

    /**
     * haskell: sconcat :: NonEmpty a -> a
     *
     * @template A
     * @param A $a
     */
    public static function sconcat($a): mixed
    {
        /*
        if ($a instanceof OrdInstance) {
            return $a->compare($b);
        }
        */

        /**
         * @var Maybe<A>
         */
        $maybe = self::singleton()->container->invoke('sconcat', $a, $a);
        if (!$maybe->isJust()) {
            throw new \TypeError('Unknown Semigroup instance');
        }

        return $maybe->getValue()->getValue();
    }

   /**
     * @template A1
     * @template Ma1
     * @template Ma2
     * @param \Closure(mixed):bool $typePredicate
     * @param \Closure(Ma1, \Closure(A1):Ma2):Ma2 $bind
     * @param \Closure(A1):Ma1 $inject
     */
    public static function register(
        \Closure $typePredicate,
        string $instanceName,
        ?\Closure $assoc = null,
        ?\Closure $sconcat = null,
    ): self
    {
        // todo: minimal is bind? could derive inject and then (although `then` has hard-coded default)
        if ($assoc === null && $sconcat === null) {
            throw new \Exception('Must provide at least one of the two implementations (assoc,sconcat)');
        }

        self::singleton()->container
            ->registerMethod(
                new Method('assoc', new Type($typePredicate, $instanceName), $assoc)
            )
            ->registerMethod(
                new Method('sconcat', new Type($typePredicate, $instanceName), $sconcat)
            );
        return self::singleton();
    }
}
