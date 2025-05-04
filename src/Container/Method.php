<?php

namespace thgs\Functional\Container;

use thgs\Functional\Typeclass\EqInstance;
use function thgs\Functional\partial;

/**
 * @implements EqInstance<Method>
 */
class Method implements
    EqInstance
{
    public function __construct(
        public readonly string $name,
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
     * Equality is defined in terms of "same type" as given by the
     * type name, the implementation is ignored.
     *
     * @param Method $other
     */
    public function equals(EqInstance $other): bool
    {
        /**
         * Checking the name name is already handled by the client of
         * this but for brevity and correctness.
         */
        return $other->name == $this->name
            && $other->type->name == $this->type->name;
    }

    /**
     * @param self $other
     * @codeCoverageIgnore
     */
    public function notEquals(EqInstance $other): bool
    {
        return !$this->equals($other);
    }
}
