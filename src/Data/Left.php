<?php

namespace thgs\Functional\Data;

/**
 * @template A
 */
final class Left
{
    /**
     * @param A $x
     */
    public function __construct(private mixed $x)
    {
    }

    /**
     * @return A 
     */
    public function getValue(): mixed
    {
        return $this->x;
    }
}
