<?php

namespace thgs\Functional\Data\Descriptive;

use thgs\Functional\Typeclass\ContravariantInstance;
use thgs\Functional\Typeclass\FunctorInstance;

use function thgs\Functional\equals;
use function thgs\Functional\notEquals;
use function thgs\Functional\partial;

/**
 * This class is the simplest form of elaborately describing all the
 * elements of a category in a diagram like way, explicitly all
 * associations.  Objects and morphisms all could be just strings and
 * the main unit is the association between two objects by a morphism.
 *
 * This will mutate the same object.
 *
 * @todo Initially was strings only, however thought to abstract the
 * actual representation of all 3 (initial, morphism, target) but the
 * problem is that with `mixed` it could have some elements with one
 * representation and some others with a different one.
 *
 * @template A
 * @implements FunctorInstance<CategoryDescription<A>>
 *
 * @todo support Contravariant ? Maybe need to add a transformation function for this.
 */
class CategoryDescription implements
    FunctorInstance
{
    /**
     * @param array<array{0: A, 1: A, 2: A}> $associations
     */
    public function __construct(private array $associations)
    {
    }

    public function addMorphism(mixed $initial, mixed $morphism, mixed $target): CategoryDescription
    {
        $found = !empty($this->withAll($initial, $morphism, $target));
        if (!$found) {
            $this->associations[] = [$initial, $morphism, $target];
        }
        return $this;
    }

    public function withInitial(string $initial): array
    {
        return $this->withAll(initial: $initial);
    }

    public function withMorphism(string $morphism): array
    {
        return $this->withAll(morphism: $morphism);
    }

    public function withTarget(string $target): array
    {
        return $this->withAll(target: $target);
    }

    public function withAny(?mixed $initial = null, ?mixed $morphism = null, ?mixed $target = null): array
    {
        $return = [];
        foreach ($this->associations as $association) {
            if (($initial && equals($initial, $association[0]))
                || ($morphism && equals($morphism, $association[1]))
                || ($target && equals($target, $association[2]))
            ) {
                $return[] = $association;
            }
        }
        return $return;
    }

    public function withAll(?mixed $initial = null, ?mixed $morphism = null, ?mixed $target = null): array
    {
        $return = [];
        foreach ($this->associations as $association) {
            if (($initial && notEquals($initial, $association[0]))
                || ($morphism && notEquals($morphism, $association[1]))
                || ($target && notEquals($target, $association[2]))
            ) {
                continue;
            }
            $return[] = $association;
        }
        return $return;
    }

    /**
     * @template B
     * @return CategoryDescription<B>
     */
    public function fmap(\Closure $f): FunctorInstance
    {
        // todo: phpstan cannot resolve this just like that. no idea why though.
        return new self(array_map(partial($f), $this->associations));
    }
}
