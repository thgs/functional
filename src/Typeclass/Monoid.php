<?php

namespace thgs\Functional\Typeclass;

use thgs\Functional\Container\Method;
use thgs\Functional\Container\MethodContainer;
use thgs\Functional\Container\Type;
use thgs\Functional\Container\TypeName;
use thgs\Functional\Data\Maybe;

class Monoid
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
     * As we cannot infer the type from the call site, passing an
     * explicit type is required.
     */
    public static function mempty(TypeName|string $asType): mixed
    {
        /**
         * @var Maybe<mixed>
         */
        $maybe = self::singleton()->container->invoke('mempty', $asType);
        if (!$maybe->isJust()) {
            throw new \TypeError('Unknown Monoid instance');
        }

        return $maybe->getValue()->getValue();
    }

    /**
     * @template A
     * @param A|MonoidInstance<A> $a
     * @param A|MonoidInstance<A> $b
     * @return ($a is MonoidInstance<A> ? MonoidInstance<A> : A)
     */
    public static function mappend(mixed $a, mixed $b, ?TypeName $asType = null): mixed
    {
        if ($a instanceof MonoidInstance && $b instanceof MonoidInstance) {
            return $a->mappend($b);
        }

        /**
         * @var Maybe<mixed>
         */
        $maybe = self::singleton()->container->invoke('mappend', $asType ?: $a, $a, $b);
        if (!$maybe->isJust()) {
            throw new \TypeError('Unknown Monoid instance');
        }

        return $maybe->getValue()->getValue();
    }

    // todo: implement mconcat

    /**
     * @template A1
     *
     * @param \Closure(mixed):bool $typePredicate
     * @param A1 $mempty
     * @param \Closure(A1,A1):A1 $mappend
     */
    public static function register(\Closure $typePredicate, mixed $mempty, \Closure $mappend, string $instanceName): self
    {
        self::singleton()->container
            ->registerMethod(
                new Method('mempty', new Type($typePredicate, $instanceName), fn () => $mempty)
            )
            ->registerMethod(
                new Method('mappend', new Type($typePredicate, $instanceName), $mappend)
            );
        return self::singleton();
    }
}
