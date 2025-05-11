<?php

namespace thgs\Functional\Typeclass;

use thgs\Functional\Container\Method;
use thgs\Functional\Container\MethodContainer;
use thgs\Functional\Container\Type;
use thgs\Functional\Data\Maybe;
use function thgs\Functional\id;
use function thgs\Functional\mappend;
use function thgs\Functional\mempty;
use function thgs\Functional\partial;
use function thgs\Functional\reflectReturnType;
use function thgs\Functional\rl;

class Foldable
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
     *
     * @template A
     * @template B
     * @template Ta
     * @template M
     *
     * @param \Closure(mixed):bool $typePredicate
     * @param \Closure(\Closure(A):M,Ta):M $foldMap
     * @param \Closure(\Closure(A,B):B,B,Ta):B $foldr
     */
    public static function register(
        string $instanceName,
        \Closure $typePredicate,
        ?\Closure $foldMap = null,
        ?\Closure $foldr = null
    ): self
    {
        // deriving methods
        if ($foldMap === null && $foldr === null) {
            throw new \Exception('Must provide at least one of the two implementations (foldMap,foldr)');
        }

        if ($foldMap === null) {
            throw new \Exception('Deriving foldMap is not yet supported');
        }

        if ($foldr === null) {
            throw new \Exception('Deriving foldr is not yet supported');
            // todo: needs appEndo
        }

        self::singleton()->container
            ->registerMethod(
                new Method('foldMap', new Type($typePredicate, $instanceName), $foldMap)
            )
            ->registerMethod(
                new Method('foldr', new Type($typePredicate, $instanceName), $foldr)
            );
        return self::singleton();
    }

    /**
     * preliminary work to deriveFoldMap
     * issue is at mempty(...)
     */
    private static function deriveFoldMap(\Closure $foldr): \Closure
    {
        return function (\Closure $monoidConstructor, $ta) use ($foldr) {
            // todo: need to infer types?
            // todo: maybe we can only support MonoidInstance?

            /**
             * Haskell:
             * foldMap :: Monoid m => (a -> m) -> t a -> m
             * foldMap                f = foldr (mappend . f) mempty
             *
             * (.) f g = \x -> f (g x)
             * (.) mappend f = \x -> mappend (f x)
             */
            // foldMap basically unwraps the value from Foldable, makes it a monoid and
            // runs mappend on the values, using foldr strategy of traversal.

            // todo: issue here is that we do not know the Monoid type
            // to do just the below:
            // return partial($foldr, rl(mappend(...), $monoidConstructor), mempty(...));

            // We could infer from $monoidConstructor's return type, however
            // its more convenient to assume that the given $ta is also a monoid
            // instance. It also must be (if user is not using the container)
            // because otherwise how could we mappend its wrapped values?

            if ($ta instanceof MonoidInstance) {
                return partial($foldr, rl(mappend(...), $monoidConstructor), $ta->mempty());
            }
        };
    }

    public static function fold(mixed $foldable): mixed
    {
        return self::foldMap(id(...), $foldable);
    }

    /**
     * @template A
     * @template M
     * @template Ta
     * @param \Closure(A):M $f
     * @param Ta|FoldableInstance<A> $foldable
     * @return ($foldable is FoldableInstance<A> ? FoldableInstance<A> : M)
     */
    public static function foldMap(\Closure $f, mixed $foldable): mixed
    {
        if ($foldable instanceof FoldableInstance) {
            return $foldable->foldMap($f);
        }

        /**
         * @var Maybe<M> $maybe
         */
        $maybe = self::singleton()->container->invoke('foldMap', $foldable, $f, $foldable);
        if (!$maybe->isJust()) {
            throw new \TypeError('Unknown Foldable instance');
        }

        return $maybe->getValue()->getValue();
    }

    /**
     * @template A
     * @template B
     * @template Ta
     * @param \Closure(A,B):B $f
     * @param B $b
     * @param Ta|FoldableInstance<A> $foldable
     * @return B
     */
    public static function foldr(\Closure $f, mixed $b, mixed $foldable): mixed
    {
        if ($foldable instanceof FoldableInstance) {
            return $foldable->foldr($f, $b);
        }

        /**
         * @var Maybe<B> $maybe
         */
        $maybe = self::singleton()->container->invoke('foldr', $foldable, $f, $b, $foldable);
        if (!$maybe->isJust()) {
            throw new \TypeError('Unknown Foldable instance');
        }

        return $maybe->getValue()->getValue();
    }
}
