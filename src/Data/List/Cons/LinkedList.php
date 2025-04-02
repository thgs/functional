<?php

namespace thgs\Functional\Data\List\Cons;

use Closure;
use thgs\Functional\Expression\Composition;
use thgs\Functional\Typeclass\FunctorInstance;

use function thgs\Functional\c;

/**
 * Haskell implements `List a` as a singly linked list.  This
 * implementation is very likely to change as more and more things are
 * brought into the library.
 *
 * Haskell lists go closer to modeling iteration rather than the
 * underlying data. Therefore this implementation is quite crucial to
 * become as efficient as possible but still general enough to support
 * the use cases that come from composition.
 *
 * @todo Add type checking
 *
 * @template A
 * @implements FunctorInstance<A>
 */
class LinkedList implements
    FunctorInstance
{
    /**
     * @param EmptyList<A>|Cons<A> $elements
     */
    public function __construct(
        private EmptyList|Cons $elements
    ) {}

    /**
     * @template A1
     * @param array<A1> $elements
     * @return LinkedList<A1>
     */
    public static function fromArray(array $elements): self
    {
        /** @var LinkedList<A1> */
        $list = self::empty();

        if (empty($elements)) {
            return $list;
        }

        foreach ($elements as $elem) {
            // todo: there must be a better way to "append" an element
            $list = $list->append(self::inject($elem));
        }
        return $list;
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
        /** @var LinkedList<A1> */
        $empty = self::empty();
        return new self(
            new Cons($head, $empty)
        );
    }

    /**
     * @phpstan-assert-if-true Cons<A> $this->elements
     * @todo fix the above does not seem to work
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
        return new self(new Cons($a, $this));
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
     * @param LinkedList<A> $ys
     * @return LinkedList<A>
     */
    public function append(self $ys): self
    {
        if ($this->elements instanceof EmptyList) {
            return $ys;
        }

        // pattern matching for reasoning.
        $x = $this->elements->head();
        $xs = $this->elements->tail();

        return $xs->append($ys)
            ->cons($x);
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

        // todo: below syntax with the tail feels more reversed than others
        return new self(
            new Cons(
                $c($this->elements->head()),
                $this->elements->tail()->fmap($f)
            )
        );
    }

    /**
     * @todo for now
     * @return array<A>
     */
    public function toArray(): array
    {
        // funny way to fold it
        $collected = [];
        /* discard result */$this->fmap(function (mixed $x) use (&$collected): mixed {
            $collected[] = $x;
            return $x; // id
        });
        return $collected;
    }

    public function length(): int
    {
        if ($this->elements instanceof EmptyList) {
            return 0;
        }

        $length = 0;
        // another funny way to fold it
        /* discard result */$this->fmap(function (mixed $x) use (&$length): mixed {
            $length++;
            return $x; // id
        });
        return $length;
    }
}
