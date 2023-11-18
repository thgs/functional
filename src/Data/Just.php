<?php

namespace thgs\Functional\Data;

use thgs\Functional\Typeclass\ShowInstance;
use function thgs\Functional\show;

/**
 * @template A
 * @implements ShowInstance<A>
 */
final class Just implements ShowInstance
{
    /**
     * @param A $value
     */
    public function __construct(private $value)
    {
    }

    /**
     * @return A
     */
    public function getValue()
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return 'Just ' . show($this->value);
    }
}