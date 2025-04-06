<?php

namespace thgs\Functional\Data;

use thgs\Functional\Typeclass\CategoryInstance;
use thgs\Functional\Typeclass\ContravariantInstance;
use function thgs\Functional\c;

/**
 * @template B
 * @template A
 *
 * @implements ContravariantInstance<Op<B,A>>
 */
class Op implements
    CategoryInstance,
    ContravariantInstance
{
    public function __construct(
        /** @var \Closure(B):A */
        private \Closure $ba
    ) {}

    /**
     * @param B $b
     * @return A
     */
    public function __invoke(mixed $b): mixed
    {
        return ($this->ba)($b);
    }

    public static function id(): callable
    {
        return fn ($x) => $x;
    }

    public static function compose(): callable
    {
        return fn ($f, $g) => c ($f) ->fmap ($g);
    }

    /**
     * @template B1
     * @param \Closure(B1):B $fba
     * @return Op<A,B1>
     */
    public function contramap(\Closure $fba): ContravariantInstance
    {
        // todo: entirely unsure if this is correct
        return new self(self::compose() ($this->ba, $fba));
    }

    /**
     * @template N of integer
     * @todo how to say that now our class `template A` MUST be `of integer`
     * @param \Closure(B):N $g
     * @return \Closure(B):int
     */
    public function addInt(\Closure $g): \Closure
    {
        return fn($a) => ($this->ba)($a) + $g($a);
    }
}
