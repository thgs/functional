<?php

namespace thgs\Functional\Container;

use thgs\Functional\Typeclass\EqInstance;
use function thgs\Functional\partial;

/**
 * @implements EqInstance<Instance>
 */
class Instance implements
    EqInstance
{
    public function __construct(
        public readonly Type $type,
        /** @var \Closure */
        public readonly \Closure $f
    ) {}

    public function predicate(mixed $value): bool
    {
        return ($this->type->predicate)($value);
    }

    public function invoke(mixed ...$xs): mixed
    {
        return partial($this->f, ...$xs);
    }

    /**
     * @param self $other
     */
    public function equals(EqInstance $other): bool
    {
        return $other->type == $this->type;
    }

    /**
     * @param self $other
     */
    public function notEquals(EqInstance $other): bool
    {
        return !$this->equals($other);
    }
}
