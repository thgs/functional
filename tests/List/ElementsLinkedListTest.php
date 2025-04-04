<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\List\Elements\LinkedList;
use thgs\Functional\Testing\FunctorLawsAssertions;

class ElementsLinkedListTest extends TestCase
{
    use FunctorLawsAssertions;

    public function testCanPrepend(): void
    {
        $l = LinkedList::inject(2);
        $l = $l->cons(1);
        $this->assertEquals([1,2], $l->toArray());
    }

    public function testCanAppend(): void
    {
        $l = LinkedList::inject(1);
        $l = $l->append(LinkedList::inject(2));
        $this->assertEquals([1,2], $l->toArray());
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

    public function testCanReturnItsLength(): void
    {
        $list = LinkedList::fromArray([1,2,3]);
        $this->assertEquals(3, $list->length());
    }
}
