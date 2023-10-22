<?php

namespace thgs\Functional\Data;

final class Left
{
    public function __construct(private $x)
    {
    }

    public function getValue()
    {
        return $this->x;
    }
}