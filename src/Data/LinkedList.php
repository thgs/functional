<?php

namespace thgs\Functional\Data;

use Closure;
use thgs\Functional\Typeclass\FunctorInstance;


/**
 * Haskell implements `List a` as a singly linked list.  This
 * implementation is very likely to change as more and more things are
 * brought into the library. For now it is implemented a layer of
 * abstraction between the list and the two possible actual data
 * wrapper objects, EmptyList and LinkedElements.
 *
 * Haskell lists go closer to modeling iteration rather than the
 * underlying data. Therefore this implementation is quite crucial to
 * become as efficient as possible but still general enough to support
 * the use cases that come from composition. Therefore it is quite
 * likely that the design and the decision to use two layers will be
 * evaluated multiple times.
 *
 * @todo Add type checking
 * @todo Decide on laziness. Even if laziness is not around the rest
 * of the library, makes sense to consider it if we model the
 * LinkedList to capture and compose iteration. Could implement
 * laziness by using generators always. Then the callee would decide
 * to trigger the yielding of the next element. Recursion can be
 * used to iterate too.
 *
 * @template A
 * @implements FunctorInstance<A>
 */
class LinkedList implements
    FunctorInstance
{
    public function __construct(
        private EmptyList|LinkedElements $elements
    ) {}

    public function cons(mixed $a)
    {
        return new LinkedList(new LinkedElements([$a, $elements->toArray()]));
    }

    public function append(self $other): self
    {
        // todo: add finite logic here, if first is not finite, return the first
        if ($this->elements instanceof EmptyList) {
            return $other; // could return a copy
        }

        if ($other->isEmpty()) {
            return $this; // could return a copy
        }

        // todo: for now just unpack all of them, but would that help
        // iteration?  this will need to adapt.
        return new self(new LinkedElements(...$this->elements, ...$other->elements));
    }

    // todo: consider the head,tail,init,last partial variations 

    /**
     * @return Maybe<Tuple<LinkedList<A>, A>>>
     */
    public function unsnoc(): Maybe
    {
        if ($this->elements instanceof EmptyList) {
            return new Maybe(new Nothing());
        }

        // todo: see haskell's implementation in terms of foldr
        // again delegation?
        return new Maybe(new Just($this->elements->unsnoc()));
    }

    public function reverse(): self
    {
        if ($this->elements instanceof EmptyList) {
            return $this; // could return a copy
        }

        return new self($this->elements->reverse());
    }

    public function isEmpty(): bool
    {
        return $this->elements instanceof EmptyList;
    }

    public function length(): int
    {
        return $this->elements instanceof EmptyList
            ? 0
            : $this->elements->length();
    }

    public function map(\Closure $f): LinkedList
    {
        if ($this->elements instanceof EmptyList) {
            return $this; // could return a new copy
        }

        // for now, to allow any other implementations of LinkedElements
        // (ie lazy) to happen we will delegate
        return new self($this->elements->map($f));
    }

    public function fmap(Closure $f): FunctorInstance
    {
        return $this->map($f);
    }

    /**
     * @template B
     * @param \Closure(B,A):B $f
     * @param B $b
     * @return self<B>
     */
    public function scanl(\Closure $f, $b): self
    {
        if ($this->elements instanceof EmptyList) {
            return $this; // could return a copy
        }

        // this is iterative and not recursive now.
        // Delegating, I think, stops the recursive implementation?
        return new self($this->elements->scanl($f, $b));
    }
}
