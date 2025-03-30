<?php

namespace thgs\Functional\Data\List\Elements;

use function thgs\Functional\t;

/**
 * @template A
 */
class LinkedElements
{
    public function __construct(
        /** @var non-empty-array<A> */
        private array $elements
    ) {
        if (empty($this->elements)) {
            throw new \TypeError('Cannot construct LinkedElements with empty array');
        }
    }

    public function length(): Int
    {
        return count($this->elements);
    }

    public function map(\Closure $f): self
    {
        $newData = [];
        foreach ($this->elements as $elem) {
            $newData[] = $f($elem);
        }
        return new self($newData);
    }

    /**
     * @return non-empty-array<A>
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * Not sure if this is right to do here.  For convenience this
     * will return a tuple in terms of its parent.
     *
     * @return Tuple<LinkedList<A>, A>
     */
    public function unsnoc(): Tuple
    {
        // implementation can be in terms of array,
        // without caring if this will be ok for "iteration"?
        // todo: maybe optimise this, do we need mutation? is it any good?

        if (count($this->elements) == 1) {
            return t(new LinkedList(new EmptyList()), array_shift($this->elements));
        }

        $last = array_pop($this->elements);
        return t(new LinkedList(new self($this->elements)), $last);
    }

    public function reverse(): self
    {
        return new self(array_reverse($this->elements));
    }

    public function scanl(callable $f, $b): self
    {
        $return = [];
        foreach ($this->elements as $elem) {
            $return[] = $f($b, $elem);
        }
        return $return;
    }
}
