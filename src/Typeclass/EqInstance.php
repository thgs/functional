<?php

namespace thgs\Functional\Typeclass;

/**
 * @template A
 */
interface EqInstance
{
    /** @return A */
    public function getValue();

    public function equals(EqInstance $other): bool;

    public function notEquals(EqInstance $other): bool;
}