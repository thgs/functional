<?php

namespace thgs\Functional\Data\List\Elements;

use Closure;
use thgs\Functional\Typeclass\FunctorInstance;

use function thgs\Functional\c;


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

    /**
     * @template A1
     * @param array<A1> $elements
     * @return LinkedList<A1>
     */
    public static function fromArray(array $elements): self
    {
        if (empty($elements)) {
            return self::empty();
        }
        return new self(new LinkedElements($elements));
    }

    /**
     * @return self<string>
     */
    public static function fromString(string $s): self
    {
        return self::fromArray(str_split($s, 1));
    }

    /**
     * @template A1
     * @return LinkedList<A1>
     */
    public static function empty(): self
    {
        /** @var EmptyList<A1> */
        $empty = new EmptyList();
        return new self($empty);
    }

    /**
     * @template A1
     * @return LinkedList<A1>
     */
    public static function inject(mixed $a): self
    {
        /** @var LinkedElements<A1> */
        $elements = new LinkedElements([$a]);
        return new self($elements);
    }

    public function isEmpty(): bool
    {
        return $this->elements instanceof EmptyList;
    }

    public function cons(mixed $a)
    {
        if ($this->elements instanceof EmptyList) {
            return self::inject($a);
        }

        $tail = $this->elements->toArray();
        return new self(new LinkedElements(array_merge([$a], $tail)));
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

        return new self(new LinkedElements(
            array_merge($this->elements->toArray(), $other->elements->toArray())
        ));
    }

    public function fmap(Closure $f): FunctorInstance
    {
        if ($this->elements instanceof EmptyList) {
            return $this;
        }

        $c = c ($f);
        return new self(new LinkedElements(
            array_map(fn ($x) => $c($x), $this->elements->toArray())
        ));
    }

    public function toArray(): array
    {
        if ($this->elements instanceof EmptyList) {
            return [];
        }

        return $this->elements->toArray();
    }

    public function length(): int
    {
        if ($this->elements instanceof EmptyList) {
            return 0;
        }

        return count($this->elements->toArray());
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
