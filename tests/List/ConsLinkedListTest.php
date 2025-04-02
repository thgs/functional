<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\List\Cons\LinkedList;
use thgs\Functional\Testing\FunctorLawsAssertions;
use function thgs\Functional\Assert\assertInstanceIsFunctor;

class ConsLinkedListTest extends TestCase
{
    use FunctorLawsAssertions;

    public function testCanAppend(): void
    {
        $l = LinkedList::inject(1);
        $l = $l->append(LinkedList::inject(2));
        $this->assertEquals([1,2], $l->toArray());
    }

    public function testIsAFunctor(): void
    {
        $list = LinkedList::fromArray([1,2,3]);

        $result = assertInstanceIsFunctor(
            $list,
            fn (int $x): int => $x * 2,
            fn (int $x): int => $x + 4
        );
        $this->assertNull($result, (string) $result);
    }

    public function testCanReturnItsLength(): void
    {
        $list = LinkedList::fromArray([1,2,3]);
        $this->assertEquals(3, $list->length());
    }
}
