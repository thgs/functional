<?php

use thgs\Functional\Data\List\Cons\LinkedList;

class ConsLinkedListBench
{
    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchCreateArrayOf100(): void
    {
        $array = range(1,100);
    }
    
    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchCreateListOf100(): void
    {
        $list = LinkedList::fromArray(range(1,100));
    }

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchAppendOnArray(): void
    {
        $array = range(1,100);
        $appended = array_merge($array, range(101, 201));
    }

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchAppendOnList(): void
    {
        $list = LinkedList::fromArray(range(1,100));
        $appended = $list->append(LinkedList::fromArray(range(101, 201)));
    }

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchPrependOnArray(): void
    {
        $array = range(101,201);
        // this would be the way but let's try to make it one item at a time to be "same" as the list implementation
        // $prepended = array_unshift($array, range(1, 100));
        foreach (range(1, 100) as $i) {
            array_unshift($array, $i);
        }
    }

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchPrependOnList(): void
    {
        $list = LinkedList::fromArray(range(101,201));
        foreach (range(1, 100) as $i) {
            $list = $list->cons($i);
        }
    }

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchCountArray(): void
    {
        $array = range(1,100);
        $length = count($array);
        
    }

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchCountList(): void
    {
        $list = LinkedList::fromArray(range(1,100));
        $length = $list->length();
    }
}
