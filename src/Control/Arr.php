<?php

namespace thgs\Functional\Control;

use thgs\Functional\Typeclass\FunctorInstance;
use thgs\Functional\Typeclass\MonadInstance;
use function thgs\Functional\partial;
use function thgs\Functional\rl;

/**
 * This tries to be (->) but we already accept a \Closure.
 *
 * @template R
 * @template A
 *
 * @todo Not sure if this should be in Control really.
 *
 * @implements FunctorInstance<R>
 * @implements MonadInstance<R>
 */
class Arr implements
    FunctorInstance,
    MonadInstance
{
    /**
     * @param \Closure(R):A $f
     */
    public function __construct(private \Closure $f)
    {
    }

    /**
     * @param callable(R):A $f
     * @return Arr<R,A>
     */
    public static function fromCallable(callable $f): self
    {
        return new self(\Closure::fromCallable($f));
    }

    /**
     * @param R ...$rs
     * @return A
     */
    public function __invoke(mixed ...$rs)
    {
        return partial($this->f, ...$rs);
    }

    /**
     * @template B
     * @param \Closure(R):B $f
     * @return Arr<R,B>
     */
    public function fmap(\Closure $f): FunctorInstance
    {
        // maybe optimise this
        return new self( rl($f, $this->f) );
    }

    /**
     * @template R
     * @template A
     * @param \Closure(R):A $a
     * @return Arr<R,A>
     */
    public static function inject(mixed $a): MonadInstance
    {
        if (!$a instanceof \Closure) {
            // for now will TypeError explicitly. It would error from the constructor though.
            throw new \TypeError('Expected Closure');
        }
        return new self($a);
    }

    /**
     * The function passed needs to accept a normal value and return a monadic
     * one.
     *
     * @template B
     * @param \Closure(R):MonadInstance<B> $f
     * @return Arr<R,B>
     */
    public function bind(\Closure $f): MonadInstance
    {
        // instance Monad ((->) r) where
        //     f >>= k = \ r -> k (f r) r
        // Monad m => m a -> (a -> m b) -> m b
        // m : (r -> )
        //
        // (r -> a) -> (a -> (r -> b)) -> (r -> b)
        // Arr<R,A> -> (a -> Arr<R,B>) -> Arr<R,B>
        // instance Monad ((->) r) where
        //     f >>= k = \ r -> k (f r) r
        // f : $this
        // k : $f
        //     $this >>= $f = \ r -> $f ($this r) r

        // This seems correct but there is a missing "r" in the end.
        return new self( fn ($r) => $f (partial($this->f, $r) ));
    }

    /**
     * (>>) :: m a -> m b -> m b
     *
     * @template B
     * @param Arr<R,B> $b
     * @return Arr<R,B>
     */
    public function then(MonadInstance $b): MonadInstance
    {
        if ($b instanceof Arr) {
            // for now we can just type error
            throw new \TypeError('Expected instanceof Arr');
        }
        return $this->bind(fn () => $b);
    }
}
