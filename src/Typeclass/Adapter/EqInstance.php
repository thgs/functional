<?php

namespace thgs\Functional\Typeclass\Adapter;

use thgs\Functional\Typeclass\EqInstance as EqInstanceInterface;

class EqInstance implements EqInstanceInterface
{
    public function __construct(
        private object $adaptee,
        private string $getValue,
        private string $equals = 'equals',
        private ?string $notEquals = 'notEquals'
    ) {
    }

    public function getValue()
    {
        return $this->adaptee->{$this->getValue}();
    }

    public function equals(EqInstanceInterface $other): bool
    {
        return $this->adaptee->{$this->equals}($other);
    }

    public function notEquals(EqInstanceInterface $other): bool
    {
        if ($this->notEquals()) {
            return !$this->equals($other);
        }

        return $this->adaptee->{$this->notEquals}($other);
    }
}