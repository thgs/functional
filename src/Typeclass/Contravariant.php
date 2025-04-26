<?php

namespace thgs\Functional\Typeclass;

use thgs\Functional\Container\Method;
use thgs\Functional\Container\MethodContainer;
use thgs\Functional\Container\Type;
use thgs\Functional\Data\Maybe;

class Contravariant
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
     *  (a' -> a) -> f a -> f a'
     *  (b -> a) -> f a -> f b
     *
     * @template A1
     * @template B1
     * @template Ca
     * @template Cb
     *
     * @param \Closure(B1):A1 $f
     * @param ContravariantInstance<A1>|Ca $fa
     * @return ContravariantInstance<B1>|Cb
     */
    public static function contramap(\Closure $f, mixed $fa): mixed
    {
        if ($fa instanceof ContravariantInstance) {
            return $fa->contramap($f);
        }

        /**
         * @var Maybe<Cb> $maybe
         */
        $maybe = self::singleton()->container->invoke('contramap', $fa, $f, $fa);
        if (!$maybe->isJust()) {
            throw new \TypeError('Unknown Contravariant instance');
        }

        return $maybe->getValue()->getValue();
    }

    /**
     * @template A
     * @template B
     * @template Ca
     * @template Cb
     *
     * @param \Closure(mixed):bool $typePredicate
     * @param \Closure(\Closure(B):A,Ca):Cb $contramap
     */
    public static function register(\Closure $typePredicate, \Closure $contramap, string $instanceName): self
    {
        self::singleton()->container->registerMethod(
            new Method('contramap', new Type($typePredicate, $instanceName), $contramap)
        );
        return self::singleton();
    }
}
