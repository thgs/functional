<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\List\Generator\LinkedList;
use thgs\Functional\Testing\FunctorLawsAssertions;
use function thgs\Functional\fmap;

class GeneratorLinkedListTest extends TestCase
{
    use FunctorLawsAssertions;

    public function testCanPrepend(): void
    {
        $l = LinkedList::inject(2);
        $l = $l->cons(1);
        $this->assertEquals([1,2], $l->toArray());
    }

    public function testCanPrependOnEmptyList(): void
    {
        $l = LinkedList::empty();
        $l = $l->cons(1);
        $this->assertEquals([1], $l->toArray());
    }

    public function testCanAppend(): void
    {
        $l = LinkedList::inject(1);
        $l = $l->append(LinkedList::inject(2));
        $this->assertEquals([1,2], $l->toArray());
    }

    public function testCanAppendOnEmptyList(): void
    {
        $l = LinkedList::empty();
        $l = $l->append(LinkedList::inject(2));
        $this->assertEquals([2], $l->toArray());
    }

    public function testCanAppendAnEmptyList(): void
    {
        $l = LinkedList::inject(1);
        $l = $l->append(LinkedList::empty());
        $this->assertEquals([1], $l->toArray());
    }

    public function testIsAFunctor(): void
    {
        $list = LinkedList::fromArray([1,2,3]);

        $this->assertInstanceIsFunctor(
            $list,
            fn (int $x): int => $x * 2,
            fn (int $x): int => $x + 4
        );
    }

    public function testCanFmapOnEmptyList(): void
    {
        $list = LinkedList::empty();
        $list = fmap(fn ($x) => $x * 2, $list);
        $this->assertEquals(0, $list->length());
    }

    public function testCanFmapOnNonEmptyList(): void
    {
        $list = LinkedList::inject(234);
        $list = fmap(fn ($x) => $x * 2, $list);
        $this->assertEquals(1, $list->length());
        $this->assertEquals([234*2], $list->toArray());
    }

    public function testCanReturnEmptyArrayOnEmptyList(): void
    {
        $list = LinkedList::empty();
        $this->assertEmpty($list->toArray());
        $this->assertEquals([], $list->toArray());
    }

    public function testCanReturnEmptyIteratorOnEmptyList(): void
    {
        $list = LinkedList::empty();
        $iterator = $list->getIterator();

        $elements = [];
        foreach ($iterator as $elem) {
            // unreachable
            $elements[] = $elem;
        }
        $this->assertEmpty($elements);
    }

    public function testCanReturnItsLength(): void
    {
        $list = LinkedList::fromArray([1,2,3]);
        $this->assertEquals(3, $list->length());
    }

    public function testCanCreateFromString(): void
    {
        $string = "Hello PHP";
        $list = LinkedList::fromString($string);

        $this->assertEquals(strlen($string), $list->length());
        $this->assertEquals($string, implode('', $list->toArray()));
    }

    public function testCanCreateEmptyList(): void
    {
        $emptyList = LinkedList::empty();
        $this->assertEquals(0, $emptyList->length());
    }

    public function testCanCreateFromEmptyArray(): void
    {
        $emptyList = LinkedList::fromArray([]);
        $this->assertEquals(0, $emptyList->length());
    }
}
