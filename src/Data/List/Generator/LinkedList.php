<?php

namespace thgs\Functional\Data\List\Generator;

use thgs\Functional\Data\ListInterface;
use thgs\Functional\Typeclass\FunctorInstance;

use function thgs\Functional\c;

/**
 * @todo Add type checking
 * @todo Use "Rewindable" generators here, but for now to see the impact it is
 * without.  Problem with those is that if they actually contain side-effects,
 * rewinding them does not guarantee same results.
 *
 * @template A
 * @implements FunctorInstance<A>
 * @implements ListInterface<A>
 */
class LinkedList implements
    FunctorInstance,
    ListInterface
{
    /**
     * @param EmptyList<A>|\Closure():\Generator<A> $generator
     */
    public function __construct(
        private EmptyList|\Closure $elements
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

        /** @var \Generator<A> */
        $generator = fn (): \Generator => yield from $elements;
        return new self($generator);
    }

    /**
     * @return self<string>
     */
    public static function fromString(string $s): self
    {
        $generator = fn (): \Generator => yield from str_split($s, 1);
        return new self($generator);
    }

    /**
     * @template A1
     * @return LinkedList<A1>
     * @todo did a silly trick with EmptyList having the type too so it can pass it.
     */
    public static function empty(): self
    {
        /** @var EmptyList<A1> */
        $emptyList = new EmptyList();
        return new self($emptyList);
    }

    /**
     * @template A1
     * @param A1 $head
     * @return LinkedList<A1>
     */
    public static function inject($head): self
    {
        /** @var \Generator<A1> */
        $generator = fn () => yield $head;
        return new self($generator);
    }

    /**
     * @phpstan-assert-if-true EmptyList $this->elements
     */
    public function isEmpty(): bool
    {
        return $this->elements instanceof EmptyList;
    }

    /**
     * @param A $a
     * @return LinkedList<A>
     */
    public function cons(mixed $a): self
    {
        if ($this->elements instanceof EmptyList) {
            return $this->inject($a);
        }

        /** @var \Generator<A> */
        $generator = function () use (&$a): \Generator {
            $key = 0;
            yield $key++ => $a;
            foreach (($this->elements)() as $x) {
                yield $key++ => $x;
            }
            //yield from ($this->elements)();
        };
        return new self($generator);
    }

    /**
     * (++) :: [a] -> [a] -> [a]
     * {-# NOINLINE [2] (++) #-}
     * -- Give time for the RULEs for (++) to fire in InitialPhase
     * -- It's recursive, so won't inline anyway,
     * -- but saying so is more explicit
     * (++) []     ys = ys
     * (++) (x:xs) ys = x : xs ++ ys
     * (++) (x:xs) ys = (:) x ((++) xs ys)
     *
     * @param ListInterface<A> $ys
     * @return LinkedList<A>
     */
    public function append(ListInterface $ys): self
    {
        if ($this->elements instanceof EmptyList) {
            return $ys;
        }

        if ($ys->isEmpty()) {
            return $this;
        }

        $generator = function () use ($ys): \Generator {
            $key = 0;
            foreach (($this->elements)() as $x) {
                yield $key++ => $x;
            }

            /**
             * To support ListInterface as parameter, we iterate from
             * \IteratorAggregate.
             */
            foreach ($ys as $x) {
                yield $key++ => $x;
            }

            // When you yield from it probably messes up the keys?
            // Then when you toArray it will overwrite keys
            //yield from ($this->elements)();
            //yield from ($ys->elements)();
        };
        return new self($generator);
    }

    /**
     * @template B
     * @param \Closure(A):B $f
     * @return LinkedList<B>
     */
    public function fmap(\Closure $f): self
    {
        if ($this->elements instanceof EmptyList) {
            return $this;
        }

        // todo: this will happen every time, in a recursive
        // implementation fmap() should allow for "composition".
        /** @var Composition<A,B> */
        $c = c ($f);

        $generator = function () use ($c): \Generator  {
            foreach (($this->elements)() as $elem) {
                yield $c($elem);
            }
        };
        return new self($generator);
    }

    /**
     * @todo for now
     * @return list<A>
     */
    public function toArray(): array
    {
        if ($this->elements instanceof EmptyList) {
            return [];
        }

        return \iterator_to_array(($this->elements)());
    }

    public function length(): int
    {
        if ($this->elements instanceof EmptyList) {
            return 0;
        }

        // assuming that \iterator_to_array and THEN count would be slightly slower

        $length = 0;
        foreach (($this->elements)() as $discard) {
            $length++;
        }
        return $length;
    }

    public function getIterator(): \Traversable
    {
        if ($this->elements instanceof EmptyList) {
            return [];
        }
        yield from ($this->elements)();
    }
}
