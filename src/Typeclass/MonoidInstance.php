<?php

namespace thgs\Functional\Typeclass;

/**
 * @template A
 * @extends SemigroupInstance<A>
 */
interface MonoidInstance extends SemigroupInstance
{
    /**
     * @return A
     */
    public function mempty(): mixed;

    /**
     * @param MonoidInstance<A> $b
     * @return MonoidInstance<A>
     */
    public function mappend(MonoidInstance $b): MonoidInstance;

    // todo: define mconcat -- needs list (OR Foldable?)
}
