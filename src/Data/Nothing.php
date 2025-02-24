<?php

namespace thgs\Functional\Data;

use thgs\Functional\Typeclass\ShowInstance;

/**
 * @template-implements ShowInstance<Nothing>
 */
final class Nothing implements ShowInstance
{
    public function getValue(): null
    {
        // todo: for now returns null.
        return null;
    }

    public function __toString(): string
    {
        return 'Nothing';
    }
}
