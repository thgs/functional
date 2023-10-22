<?php

namespace thgs\Functional\Data;

use thgs\Functional\Typeclass\EqInstance;
use thgs\Functional\Typeclass\FunctorInstance;
use thgs\Functional\Typeclass\ShowInstance;

use function thgs\Functional\show;

/**
 * @template A
 * @template B
 */
class Either implements
    EqInstance,
    ShowInstance,
    FunctorInstance
{
    /**
     * @param Left<A>|Right<B> $x
     */
    public function __construct(private readonly Left|Right $x)
    {
    }

    public function getValue(): Left|Right
    {
        return $this->x;
    }

    /**
     * @param Either $other
     */
    public function equals(EqInstance $other): bool
    {
        if (!$other instanceof Either) {
            throw new \TypeError('Expecting Either');
        }

        // todo: probably breaks with callables here, maybe need a function "equals" ?
        return $other->x == $this->x;
    }

    public function notEquals(EqInstance $other): bool
    {
        return !$this->equals($other);
    }

    public function __toString(): string
    {
        return ($this->x instanceof Left ? 'Left ' : 'Right ') . show($this->x->getValue());
    }

    public function fmap(callable $f): FunctorInstance
    {
        return match (\true) {
            $this->x instanceof Left => new static(clone $this->x),
            $this->x instanceof Right => new static(new Right( $f($this->x->getValue()) ))
        };
    }
}