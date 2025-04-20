<?php

namespace thgs\Functional\Typeclass;

use thgs\Functional\Container\Method;
use thgs\Functional\Container\MethodContainer;
use thgs\Functional\Container\Type;
use thgs\Functional\Data\Maybe;

class Functor
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
     * @template Fa
     * @template Fb
     *
     * @param \Closure(A1):B1 $f
     * @param FunctorInstance<A1>|Fa $fa
     * @return FunctorInstance<B1>|Fb
     */
    public static function fmap(\Closure $f, mixed $fa): mixed
    {
        if ($fa instanceof FunctorInstance) {
            return $fa->fmap($f);
        }

        /**
         * @var Maybe<Fb> $maybe
         */
        $maybe = self::singleton()->container->invoke('fmap', $fa, $f, $fa);
        if (!$maybe->isJust()) {
            throw new \TypeError('Unknown Functor instance');
        }

        return $maybe->getValue()->getValue();
    }

    /**
     * Usage:
     *
     * Functor::register(fmap: fn ($x) => $x, typePredicate: is_int(...), instanceName: 'int')
     *
     * @template A
     * @template B
     * @template Fa
     * @template Fb
     *
     * @param \Closure(mixed):bool $typePredicate
     * @param \Closure(\Closure(A):B,Fa):Fb $fmap
     */
    public static function register(\Closure $typePredicate, \Closure $fmap, string $instanceName): self
    {
        /**
         * Here we register each method implementation in the single container
         * here is where the deriving will happen so if we had a way to derive
         * Functor implementation from a data type, we could do it here and
         * register it only if given a class name
         */

        /**
         * todo: if instanceName is a class that implements FunctorInstance then
         * there is no need to register (could support override this way)
         */

        self::singleton()->container->registerMethod(
            new Method('fmap', new Type($typePredicate, $instanceName), $fmap)
        );
        return self::singleton();
    }
}
