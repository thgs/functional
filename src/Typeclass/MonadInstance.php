<?php

namespace thgs\Functional\Typeclass;

/**
 * @template A
 * Prelude now defines Monad with a type constraint to be an Applicative.
 */
interface MonadInstance
{
    /**
     * Equivalent of Haskell's `return`
     * @param A $a
     * @return MonadInstance<A>
     * @todo or maybe use `pure`
     */
    public static function inject(mixed $a): MonadInstance;

    /**
     * (>>=) :: m a -> (a -> m b) -> m b
     *
     * m a is the current object that implements this interface.
     *
     * Therefore we only need to accept (a -> m b)
     * Could also be named `apply`.
     *
     * The function passed needs to accept a normal value and return a monadic
     * one.
     *
     * @template B
     * @param callable(A):MonadInstance<B>
     * @return MonadInstance<B>
     */
    public function bind(callable $f): MonadInstance;

    /**
     * @todo need default implementation
     * @return MonadInstance<A>
     */
    //public function sequenceAndDiscardFirst(): MonadInstance;

    /**
     * @return MonadInstance<A>
     * @todo This has moved to MonadFail now in Haskell. Not sure yet to include
     * or not.
     */
    // public function fail(string $message): MonadInstance;
}
