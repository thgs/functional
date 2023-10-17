<?php

namespace thgs\Functional\Data;

/**
 * @template A
 */
class Just
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
}