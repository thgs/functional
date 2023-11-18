<?php

namespace thgs\Functional\Typeclass\Adapter;

use thgs\Functional\Typeclass\FunctorInstance as FunctorInstanceInterface;

class FunctorInstance implements FunctorInstanceInterface
{
    public function __construct(
        private object $decorated,
        private string $fmap
    ) {
    }

    public function fmap(callable $f): FunctorInstanceInterface
    {
        return $this->decorated->{$this->fmap}($f);
    }
}