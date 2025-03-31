<?php

namespace thgs\Functional\Data;

/**
 * @template A
 * @implements \IteratorAggregate<A>
 */
class NonEmptyArray implements \IteratorAggregate
{
    public function __construct(
        /**
         * @var non-empty-array<A>
         */
        private array $elements
    ) {
        if (empty($this->elements)) {
            throw new \TypeError('Given array is empty.');
        }
    }

    /**
     * @return \Generator<array-key, A, void, void>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->elements;
    }

    /**
     * @return non-empty-array<A>
     */
    public function toArray(): array
    {
        return $this->elements;
    }
}

