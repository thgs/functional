<?php

namespace thgs\Functional\Data;

use thgs\Functional\Typeclass\EqInstance;
use thgs\Functional\Typeclass\ShowInstance;

use function thgs\Functional\show;

/**
 * @template A
 * @template B
 *
 * @implements EqInstance<Either<A,B>>
 * @implements ShowInstance<Either<A,B>>
 *
 * implements FunctorInstance<Either<A,B>>
 */
class Either implements
    EqInstance,
    ShowInstance
    /*FunctorInstance*/
{
    /**
     * @param Left<A>|Right<B> $x
     */
    public function __construct(private readonly Left|Right $x)
    {
    }

    /**
     * @return Left<A>|Right<B>
     */
    public function getValue(): Left|Right
    {
        return $this->x;
    }

    /**
     * @phpstan-assert-if-true Right<B> $this->x
     * @phpstan-assert-if-true Right<B> $this->getValue()
     */
    public function isRight(): bool
    {
        return $this->x instanceof Right;
    }

    /**
     * @param EqInstance<*>|Either<*,*> $other
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

    /**
     * @todo correct this, Either is : Functor (Either a)
     *
    public function fmap(callable $f): FunctorInstance
    {
        return match (\true) {
            $this->x instanceof Left => new self(clone $this->x),
            $this->x instanceof Right => new self(new Right( c ($f) ($this->x->getValue()) ))
        };
    }
    */
}
