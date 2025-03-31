<?php

namespace thgs\Functional\Data\List\Cons;

use thgs\Functional\Data\List\Cons\LinkedList;
use function thgs\Functional\t;

/**
 * @template A
 */
class Cons
{
    public function __construct(
        /** @var A */
        private $head,
        /** @var LinkedList<A> */
        private LinkedList $tail
    ) {
        // todo: syntax is pretty hard, maybe $tail is nullable and then becomes EmptyList?
    }

    /**
     * @return A
     */
    public function head(): mixed
    {
        return $this->head;
    }

    /**
     * @return LinkedList<A>
     */
    public function tail(): LinkedList
    {
        return $this->tail;
    }

    //
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
