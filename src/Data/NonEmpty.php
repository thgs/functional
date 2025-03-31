<?php

namespace thgs\Functional\Data;

/**
 * @template A of non-empty-string|non-empty-array|true|non-zero-int|object
 * @todo there is no non-zero float
 *
 * This could be a typeclass and then NonEmptiness be defined for
 * *any* type, when the non-emptiness is defined differently than the
 * PHP's `empty()` behaviour.  For example, Maybe has Nothing which
 * should be considered `empty`.
 *
 * @todo what about resources?
 * @todo what about \Generators ?
 * @todo what about Fibers?
 */
class NonEmpty
{
    public function __construct(
        /** @var A $a */
        private mixed $a
    ) {
        if (empty($this->a)) {
            throw new \TypeError('Given value is empty.');
        }

        // todo: wrap around resources, \Generators, Fibers, whatnot
        // is not following PHP's empty and is already available.
        // todo: add the typeclass check here, if $a implements it.
    }

    // todo: define fromNonEmptyArray where we lift `NonEmpty` into the list/array

    /**
     * @return A
     */
    public function nonEmptyValue(): mixed
    {
        return $this->a;
    }
}

