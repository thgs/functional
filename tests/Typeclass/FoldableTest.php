<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Typeclass\Foldable;
use thgs\Functional\Typeclass\Monoid;
use function thgs\Functional\foldMap;
use function thgs\Functional\foldr;
use function thgs\Functional\mappend;
use function thgs\Functional\rl;

class FoldableTest extends TestCase
{
    public function testCanFoldrARegisteredInstance(): void
    {
        /**
         * @todo fix the container states!
         * Here we need to register "php-array" as an instance for Monoid
         * so we can use `mappend`
         */
        Monoid::register(
            instanceName: 'php-array',
            typePredicate: is_array(...),
            mempty: [],
            mappend: array_merge(...)
        );

        // hopefully these might be correct?
        $foldr = function (\Closure $k, $z, array $foldable) {
            $last = $z;
            foreach (array_reverse($foldable) as $y) {
                $last = $k($y, $last);
            }
            return $last;
        };

        $foldMap = fn (\Closure $monoidConstructor, array $foldable) =>
            $foldr(
                rl(mappend(...), $monoidConstructor),
                // passing mempty manually here
                [],
                $foldable
            );

        Foldable::register(
            instanceName: 'php-array',
            typePredicate: is_array(...),
            foldMap: $foldMap,
            foldr: $foldr
        );

        $this->assertTrue(
            foldr(
                fn (bool $a, bool $b): bool => $a || $b,
                false,
                [false, true, false]
            )
        );

        $this->assertTrue(
            foldr(
                fn (bool $a, bool $b): bool => $a && $b,
                true,
                [true, true, true]
            )
        );
    }
}
