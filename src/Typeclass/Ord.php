<?php

namespace thgs\Functional\Typeclass;

use thgs\Functional\Container\Method;
use thgs\Functional\Container\MethodContainer;
use thgs\Functional\Container\Type;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Data\Ordering;
use function thgs\Functional\equals;

class Ord
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

    public static function compare(mixed $a, mixed $b): Ordering
    {
        if ($a instanceof OrdInstance) {
            return $a->compare($b);
        }

        /**
         * @var Maybe<Ordering>
         */
        $maybe = self::singleton()->container->invoke('compare', $a, $a, $b);
        if (!$maybe->isJust()) {
            throw new \TypeError('Unknown Ord instance');
        }

        return $maybe->getValue()->getValue();
    }

    public static function lessOrEqual(mixed $a, mixed $b): bool
    {
        if ($a instanceof OrdInstance) {
            return $a->lessOrEqual($b);
        }

        /**
         * @var Maybe<bool>
         */
        $maybe = self::singleton()->container->invoke('lessOrEqual', $a, $a, $b);
        if (!$maybe->isJust()) {
            throw new \TypeError('Unknown Ord instance');
        }

        return $maybe->getValue()->getValue();
    }

    public static function less(mixed $a, mixed $b): bool
    {
        return !self::lessOrEqual($b, $a);
    }


    public static function more(mixed $a, mixed $b): bool
    {
        return !self::lessOrEqual($a, $b);
    }

    public static function moreOrEqual(mixed $a, mixed $b): bool
    {
        return self::lessOrEqual($b, $a);
    }

    public static function max(mixed $a, mixed $b): mixed
    {
        return self::lessOrEqual($a, $b) ? $b : $a;
        
    }

    public static function min(mixed $a, mixed $b): mixed
    {
        return self::lessOrEqual($a, $b) ? $a : $b;
    }

    /**
     * @template A
     * @param \Closure(mixed):bool $typePredicate
     * @param \Closure(A,A):Ordering|null $compare
     * @param \Closure(A,A):bool|null $lessOrEqual
     */
    public static function register(
        string $instanceName,
        \Closure $typePredicate,
        ?\Closure $compare = null,
        ?\Closure $lessOrEqual = null
    ): self
    {
        // deriving methods
        if ($compare === null && $lessOrEqual === null) {
            throw new \Exception('Must provide at least one of the two implementations (compare,lessOrEqual)');
        }

        if ($compare === null) {
            $compare = fn (mixed $x, mixed $y): Ordering =>
                equals($x, $y)
                    ? Ordering::EQ
                    : ($lessOrEqual($x, $y)
                       ? Ordering::LT
                       : Ordering::GT);
        }

        if ($lessOrEqual === null) {
            $lessOrEqual = fn (mixed $x, mixed $y): bool =>
                $compare($x, $y) !== Ordering::GT;
        }

        self::singleton()->container
            ->registerMethod(
                new Method('compare', new Type($typePredicate, $instanceName), $compare)
            )
            ->registerMethod(
                new Method('lessOrEqual', new Type($typePredicate, $instanceName), $lessOrEqual)
            );
        return self::singleton();
    }
}
