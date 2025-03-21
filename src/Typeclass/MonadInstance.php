<?php

namespace thgs\Functional\Typeclass;

/**
 * Prelude now defines Monad with a type constraint to be an Applicative.
 *
 * This :: m a
 * ie  MonadInstance<A> means Maybe Int so it should be MonadInstance<Maybe<Int>> ?
 * then bind is :: m a                       -> (a   -> m b        ) -> m b
 * which should :: MonadInstance<Maybe<Int>> -> (Int -> Maybe<Bool>) -> Maybe<Bool>
 * and inject   :: a                         -> m a
 * which is     :: Int                       -> Maybe<Int>
 *
 * so an implements tag should say MonadInstance<Maybe<A1>> ?
 *
 * @template A
 */
interface MonadInstance
{
    /**
     * Equivalent of Haskell's `return`
     *
     * @template A1
     * @psalm-param A1 $a
     * @return MonadInstance<A1>
     *
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
     * @param \Closure(A):MonadInstance<B> $f
     * @return MonadInstance<B>
     */
    public function bind(\Closure $f): MonadInstance;

    /**
     * @todo need default implementation
     * @return MonadInstance<A>
     */
    //public function sequenceAndDiscardFirst(): MonadInstance;

    /**
     * (>>) :: m a -> m b -> m b
     *
     * @template B
     * @param MonadInstance<B> $b
     * @return MonadInstance<B>
     */
    public function then(MonadInstance $b): MonadInstance;

    /**
     * @return MonadInstance<A>
     * @todo This has moved to MonadFail now in Haskell. Not sure yet to include
     * or not.
     */
    // public function fail(string $message): MonadInstance;
}
