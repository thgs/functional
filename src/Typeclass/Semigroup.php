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
     * @param A|SemigroupInstance<A> $a
     * @param A|SemigroupInstance<A> $b
     * @return ($a is SemigroupInstance<A> ? SemigroupInstance<A> : A)
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
     * @param iterable<A> $a
     * @return A
     */
    public static function sconcat(iterable $a): mixed
    {
        // here we will have to start iterating to find if the
        // elements are SemigroupInterface or not
        throw new \Exception('`sconcat` is not supported yet');

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
     * @param \Closure(mixed):bool $typePredicate
     * @param \Closure(A1,A1):A1|null $assoc
     * @param \Closure(iterable<A1>):A1|null $sconcat
     */
    public static function register(
        \Closure $typePredicate,
        string $instanceName,
        ?\Closure $assoc = null,
        ?\Closure $sconcat = null,
    ): self
    {
        if ($assoc === null && $sconcat === null) {
            throw new \Exception('Must provide at least one of the two implementations (assoc,sconcat)');
        }

        if ($assoc === null) {
            $assoc = fn (mixed $a, mixed $b): mixed => $sconcat ([$a, $b]);
        }

        if ($sconcat === null) {
            throw new \Exception('Deriving `sconcat` is not supported yet');
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
