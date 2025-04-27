<?php

namespace thgs\Functional\Typeclass;

use thgs\Functional\Container\Method;
use thgs\Functional\Container\MethodContainer;
use thgs\Functional\Container\Type;
use thgs\Functional\Data\Maybe;

class Show
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

    public static function show(mixed $a): string
    {
        if ($a instanceof ShowInstance) {
            return $a->__toString();
        }

        /**
         * @var Maybe<string>
         */
        $maybe = self::singleton()->container->invoke('show', $a, $a);
        
        if (!$maybe->isJust()) {
            /**
             * Allow method container to override but if given any of
             * array type, scalar or \Stringable instance, we provide
             * some defaults here to keep it sane.
             */

            if (is_array($a)) {
                return Show::showArray($a);
            }

            if (is_bool($a)) {
                return $a ? 'true' : 'false';
            }

            if (is_scalar($a) || $a instanceof \Stringable) {
                return (string) $a;
            }

            throw new \TypeError('Unknown Show instance');
        }

        return $maybe->getValue()->getValue();
    }

    /**
     * @param array<mixed> $xs
     */
    public static function showArray(array $xs): string
    {
        return '[' . implode(',', array_map(Show::show(...), $xs)) . ']';
    }

    /**
     * Usage:
     *
     * Show::register(show: fn ($x) => "Integer: " . (string) $x, typePredicate: is_int(...), instanceName: 'int')
     *
     * @template A
     *
     * @param \Closure(mixed):bool $typePredicate
     * @param \Closure(A):string $show
     */
    public static function register(\Closure $typePredicate, \Closure $show, string $instanceName): self
    {
        self::singleton()->container->registerMethod(
            new Method('show', new Type($typePredicate, $instanceName), $show)
        );
        return self::singleton();
    }
}
