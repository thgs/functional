<?php

namespace thgs\Functional\Container;

use thgs\Functional\Typeclass\EqInstance;
use thgs\Functional\Typeclass\ShowInstance;

/**
 * This is just a "special" type to convey that it carries a type name
 *
 * @implements EqInstance<TypeName>
 */
final readonly class TypeName implements
    EqInstance,
    ShowInstance
{
    public function __construct(public string $name) {}

    /**
     * @param TypeName $other
     */
    public function equals(EqInstance $other): bool
    {
        return $other instanceof TypeName && $this->name == $other->name;
    }

    public function notEquals(EqInstance $other): bool
    {
        return !$this->equals($other);
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
