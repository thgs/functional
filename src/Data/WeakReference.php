<?php

namespace thgs\Functional\Data;

use Closure;
use thgs\Functional\Typeclass\FunctorInstance;
use function thgs\Functional\partial;

/**
 * @template A of object
 *
 * @implements FunctorInstance<A>
 */
class WeakReference implements
    FunctorInstance
{
    /** @var \WeakReference<A> */
    private \WeakReference $ref;

    /**
     * @param A $object
     */
    final public function __construct(object $object)
    {
        $this->ref = \WeakReference::create($object);
    }

    /**
     * @return Maybe<A>
     */
    public function get(): Maybe
    {
        /** @var A|null $value */
        $value = $this->ref->get();
        if ($value === null) {
            /** @var Maybe<A> */
            $return = new Maybe(new Nothing());
            return $return;
        }
        return new Maybe(new Just($value));
    }

    /**
     * @template B1 of object
     * @param \Closure(A):B1 $f
     * @return self<B1>
     */
    public function fmap(Closure $f): FunctorInstance
    {
        /** @var A|null $value */
        $value = $this->ref->get();
        if ($value === null) {
            return $this;
        }

        return new self(partial($f, $value));
    }
}
