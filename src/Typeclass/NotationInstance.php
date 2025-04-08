<?php

namespace thgs\Functional\Typeclass;

/**
 * @template Element
 * @todo add composition? and preorder?
 */
interface NotationInstance
{
    /**
     * @param Element[] ...$elements
     * @return Element
     */
    public function composeMany(mixed ...$elements): mixed;
}
