<?php

namespace thgs\Functional\Typeclass\Attribute;

use Attribute;

#[Attribute]
class FunctorInstance
{
    public function __construct(private string $fmap)
    {
    }
}