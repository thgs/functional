<?php

namespace thgs\Functional\Typeclass\Attribute;

use Attribute;

#[Attribute]
class EqInstance
{
    public function __construct(private string $equals)
    {
    }
}