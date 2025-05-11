<?php

namespace thgs\Functional\Typeclass;

/**
 * @template A
 */
interface SemigroupInstance
{
    /**
     * @param SemigroupInstance<A> $other
     * @return SemigroupInstance<A>
     */
    public function assoc(SemigroupInstance $other): SemigroupInstance;

    /**
     * @todo fix NonEmpty below
     * @param iterable<SemigroupInstance<A>> $nonEmpty
     * @return A
     */
    public static function sconcat(iterable $nonEmpty): mixed;
}
