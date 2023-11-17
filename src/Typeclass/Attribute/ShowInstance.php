<?php

namespace thgs\Functional\Typeclass\Attribute;

use Attribute;

// todo: helpful read here https://wiki.haskell.org/Typeclassopedia

#[Attribute]
class ShowInstance
{
    public function __construct(private string $show = '__toString')
    {
    }
}