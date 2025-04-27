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

    public static function mappend(mixed $a, mixed $b, ?TypeName $asType = null): mixed
    {
        // todo: what is the status of that MonoidInstance? I do not even look.

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
