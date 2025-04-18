<?php

namespace thgs\Functional\Container;

class Type
{
    public function __construct(
        /** @var \Closure(mixed):bool */
        public readonly \Closure $predicate,
        public readonly string $name
    ) {}
}
