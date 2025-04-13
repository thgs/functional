<?php

namespace thgs\Functional\Data;

/**
 * @template A
 * @extends \IteratorAggregate<A>
 *
 * This might not be needed and we could "default" to a single list
 * implementation.
 */
interface ListInterface extends \IteratorAggregate
{
    /**
     * @template A1
     * @param list<A1> $elements
     * @return self<A1>
     */
    public static function fromArray(array $elements): self;

    /**
     * @template A1
     * @return self<A1>
     */
    public static function empty(): self;

    public function isEmpty(): bool;

    /**
     * @param A $a
     * @return self<A>
     */
    public function cons(mixed $a): self;

    /**
     * @param ListInterface<A> $ys
     * @return self<A>
     */
    public function append(ListInterface $ys): self;

    /**
     * @return list<A>
     */
    public function toArray(): array;

    public function length(): int;
}
