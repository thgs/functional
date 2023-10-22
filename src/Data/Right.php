<?php

namespace thgs\Functional\Data;

final class Right
{
    public function __construct(private $x)
    {
    }

    public function getValue()
    {
        return $this->x;
    }
}