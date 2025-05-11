<?php

namespace thgs\Functional\Data\Monoid;

use thgs\Functional\Typeclass\EqInstance;
use thgs\Functional\Typeclass\MonoidInstance;
use thgs\Functional\Typeclass\SemigroupInstance;
use function thgs\Functional\equals;

/**
 * This is not as generic as Haskell's, it only works for int|float
 *
 * @implements EqInstance<int|float>
 * @implements SemigroupInstance<int|float>
 * @implements MonoidInstance<int|float>
 */
class Sum implements
    EqInstance,
    SemigroupInstance,
    MonoidInstance
{
    public function __construct(
        private int|float $a
    ) {}

    /**
     * @param Sum $other
     */
    public function equals(EqInstance $other): bool
    {
        /** @phpstan-ignore instanceof.alwaysTrue */
        if (!$other instanceof Sum) {
            return false;
        }

        return equals($this->a, $other->a);
    }

    /**
     * @param Sum $other
     */
    public function notEquals(EqInstance $other): bool
    {
        return !$this->equals($other);
    }

    public function mappend(MonoidInstance $other): MonoidInstance
    {
        return $this->assoc($other);
    }

    public function mempty(): mixed
    {
        return 0;
    }

    /**
     * @return Sum
     */
    public function assoc(SemigroupInstance $other): SemigroupInstance
    {
        if (!$other instanceof Sum) {
            throw new \TypeError('Expected instance of Sum');
        }

        return new Sum($this->a + $other->a);
    }

    public static function sconcat(iterable $nonEmpty): mixed
    {
        // silly below for now
        $sum = 0;
        foreach ($nonEmpty as $i) {
            if (!$i instanceof Sum) {
                throw new \TypeError('Expected instance of Sum');
            }
            $sum += $i->a;
        }
        return $sum;
    }
}
