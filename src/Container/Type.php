<?php

namespace thgs\Functional\Container;

class Type
{
    public readonly TypeName $name;

    public function __construct(
        /** @var \Closure(mixed):bool */
        public readonly \Closure $predicate,
        string $name
    ) {
        $this->name = new TypeName($name);
    }
}
