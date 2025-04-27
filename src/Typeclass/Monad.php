<?php

namespace thgs\Functional\Typeclass;

use thgs\Functional\Container\Method;
use thgs\Functional\Container\MethodContainer;
use thgs\Functional\Container\Type;
use thgs\Functional\Container\TypeName;
use thgs\Functional\Control\Typeclass\MonadInstance;

class Monad
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
     * @template Ma1 
     * @template Mb1 
     * @param MonadInstance<A1>|Ma1 $ma
     * @param \Closure(A1):MonadInstance<B1>|Mb1 $f
     *
     * @todo the below does not seem to work
     * @param \Closure(A1):($ma is MonadInstance<A1> ? MonadInstance<B1> : Mb1) $f
     *
     * @return ($ma is MonadInstance<A1> ? MonadInstance<B1> : Mb1)
     */
    public static function bind(mixed $ma, \Closure $f): mixed
    {
        if ($ma instanceof MonadInstance) {
            return $ma->bind($f);
        }

        $maybe = self::singleton()->container->invoke('bind', $ma, $ma, $f);
        if (!$maybe->isJust()) {
            throw new \TypeError('Unknown Monad instance');
        }

        return $maybe->getValue()->getValue();
    }

    public static function then(mixed $ma, mixed $mb): mixed
    {
        return Monad::bind($ma, fn () => $mb);
    }

    public static function inject(mixed $a, TypeName $asType): mixed
    {
        $maybe = self::singleton()->container->invoke('inject', $asType);
        if (!$maybe->isJust()) {
            throw new \TypeError('Unknown Monad instance');
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
        \Closure $bind,
        \Closure $inject,
        string $instanceName
    ): self
    {
        // todo: minimal is bind? could derive inject and then (although `then` has hard-coded default)

        self::singleton()->container
            ->registerMethod(
                new Method('bind', new Type($typePredicate, $instanceName), $bind)
            )
            ->registerMethod(
                new Method('inject', new Type($typePredicate, $instanceName), $inject)
            );
        return self::singleton();
    }
}
