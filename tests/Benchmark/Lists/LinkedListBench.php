<?php

use thgs\Functional\Data\List\Cons\LinkedList as ConsLinkedList;
use thgs\Functional\Data\List\Elements\LinkedList as ElementsLinkedList;
use thgs\Functional\Data\List\Generator\LinkedList as GeneratorLinkedList;

class LinkedListBench
{
    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchArrayCreate100(): void
    {
        $array = range(1,100);
    }
    
    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchElementsListCreate100(): void
    {
        $list = ElementsLinkedList::fromArray(range(1,100));
    }

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchConsListCreate100(): void
    {
        $list = ConsLinkedList::fromArray(range(1,100));
    }

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchGeneratorListCreate100(): void
    {
        $list = GeneratorLinkedList::fromArray(range(1,100));
    }

    // -----

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchArrayAppend(): void
    {
        $array = range(1,100);
        $appended = array_merge($array, range(101, 201));
    }

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchElementsListAppend(): void
    {
        $list = ElementsLinkedList::fromArray(range(1,100));
        $appended = $list->append(ElementsLinkedList::fromArray(range(101,201)));
    }

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchConsListAppend(): void
    {
        $list = ConsLinkedList::fromArray(range(1,100));
        $appended = $list->append(ConsLinkedList::fromArray(range(101, 201)));
    }

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchGeneratorListAppend(): void
    {
        $list = GeneratorLinkedList::fromArray(range(1,100));
        $appended = $list->append(GeneratorLinkedList::fromArray(range(101, 201)));
    }

    // -----

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchArrayPrepend(): void
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
    public function benchElementsListPrepend(): void
    {
        $list = ElementsLinkedList::fromArray(range(101,201));
        foreach (range(1, 100) as $i) {
            $list = $list->cons($i);
        }
    }

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchConsListPrepend(): void
    {
        $list = ConsLinkedList::fromArray(range(101,201));
        foreach (range(1, 100) as $i) {
            $list = $list->cons($i);
        }
    }

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchGeneratorListPrepend(): void
    {
        $list = GeneratorLinkedList::fromArray(range(101,201));
        foreach (range(1, 100) as $i) {
            $list = $list->cons($i);
        }
    }

    // -----

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchArrayPrependAndIterate(): void
    {
        $array = range(101,201);
        // this would be the way but let's try to make it one item at a time to be "same" as the list implementation
        // $prepended = array_unshift($array, range(1, 100));
        foreach (range(1, 100) as $i) {
            array_unshift($array, $i);
        }

        // just to be closer to the other two, instead could do a foreach here
        array_map(fn ($x) => $x, $array);
    }

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchArrayPrependAndIterateWithForeach(): void
    {
        $array = range(101,201);
        // this would be the way but let's try to make it one item at a time to be "same" as the list implementation
        // $prepended = array_unshift($array, range(1, 100));
        foreach (range(1, 100) as $i) {
            array_unshift($array, $i);
        }

        $id = fn ($x) => $x;
        foreach ($array as $x) {
            $id ($x);
        }
    }

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchElementsListPrependAndIterate(): void
    {
        $list = ElementsLinkedList::fromArray(range(101,201));
        foreach (range(1, 100) as $i) {
            $list = $list->cons($i);
        }

        $list->fmap(fn ($x) => $x); // fmap id
    }

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchConsListPrependAndIterate(): void
    {
        $list = ConsLinkedList::fromArray(range(101,201));
        foreach (range(1, 100) as $i) {
            $list = $list->cons($i);
        }

        $list->fmap(fn ($x) => $x); // fmap id
    }

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchGeneratorListPrependAndIterate(): void
    {
        $list = GeneratorLinkedList::fromArray(range(101,201));
        foreach (range(1, 100) as $i) {
            $list = $list->cons($i);
        }

        $list->fmap(fn ($x) => $x); // fmap id
    }

    // -----

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchArrayCount(): void
    {
        $array = range(1,100);
        $length = count($array);
        
    }

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchElementsListCount(): void
    {
        $list = ElementsLinkedList::fromArray(range(1,100));
        $length = $list->length();
    }

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchConsListCount(): void
    {
        $list = ConsLinkedList::fromArray(range(1,100));
        $length = $list->length();
    }

    /**
     * @Iterations(10)
     * @Revs(500)
     */
    public function benchGeneratorListCount(): void
    {
        $list = GeneratorLinkedList::fromArray(range(1,100));
        $length = $list->length();
    }
}
