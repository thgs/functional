<?php

namespace thgs\Functional\Typeclass\Attribute;

use Attribute;

// todo: helpful read here https://wiki.haskell.org/Typeclassopedia

#[Attribute]
class FunctorInstance
{
    public function __construct(private string $fmap)
    {
    }
}