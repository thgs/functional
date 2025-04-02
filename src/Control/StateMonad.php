<?php

namespace thgs\Functional\Control;

use Closure;
use thgs\Functional\Control\Typeclass\MonadInstance;
use thgs\Functional\Data\Tuple;

use function thgs\Functional\partial;
use function thgs\Functional\t;

/**
 * @template S
 * @template A
 *
 * newtype State s a = State (s -> (a, s))
 * newtype State s a = State { runState :: s -> (a, s) }
 *
 * @implements MonadInstance<StateMonad<S,A>>
 */
class StateMonad implements
    MonadInstance
{
    public function __construct(
        /**
         * @var \Closure(S):Tuple<A,S> $f
         */
        private \Closure $f
    ) {
    }

    /**
     * Only purpose of this, I guess, is to be able to write
     *
     * StateMonad::state(...)
     *
     * Or equally,
     *
     * partial (StateMonad::state(...))
     *
     * and pass this around.
     *
     * @template S1
     * @template A1
     * @param \Closure(S1):Tuple<A1,S1> $f
     * @return StateMonad<S1,A1>
     */
    public static function state(\Closure $f): StateMonad
    {
        return new self($f);
    }

    /**
     * runState :: State s a -> s -> (a, s)
     * runState (State f) s = f s
     *
     * To help the first implementation for bind(), this is
     * implemented as static.
     * 
     * @param StateMonad<S,A> $sa
     * @param S $s
     * @return Tuple<A,S>
     *
     * @todo this in 8.4 could be implemented as a getter?
     */
    public static function runState(StateMonad $sa, mixed $s): Tuple
    {
        return partial ($sa->f, $s);
    }

    /**
     * @return StateMonad<S,S>
     */
    public function get(): StateMonad
    {
        return new self( fn ($s) => t($s, $s));
    }

    /**
     * @todo: replace null from here
     *
     * @template S1
     * @param S1 $sA
     * @return StateMonad<S1, null>
     */
    public function put(mixed $sA): StateMonad
    {
        return new self( fn ($s) => t(null, $sA));
    }

    /**
     * @template A0
     * @template S0
     * @param A0 $a
     * @return StateMonad<S0,A0>
     *
     * return a = State (\s -> (a, s))
     *
     * return :: a -> State s a
     * return x = state ( \ s -> (x, s) )
     */
    public static function inject($a): MonadInstance
    {
        return new self(
            /** @var \Closure(S0):Tuple<A0,S0> */
            fn ($s): Tuple => t($a, $s));
    }

    /**
     * sa >>= k = State (\s -> let (a, s') = runState sa s 
     *                         in runState (k a) s')
     *
     * (>>=) :: m a -> (a -> m b) -> m b
     * Here it is with m being (State s).
     * (>>=) :: State s a -> (a -> State s b) -> State s b
     *
     * @template B1
     * @param \Closure(A):StateMonad<S,B1> $k
     * @return StateMonad<S,B1>
    */
    public function bind(Closure $k): MonadInstance
    {
        $f = function ($s) use ($k) {
            $az = static::runState ($this, $s);
            [$a, $z] = [$az->fst(), $az->snd()]; // just for brevity, for now

            $qState = partial ($k, $a);
            return static::runState ($qState, $z);
        };

        return StateMonad::state($f);
    }

    /**
     * @template S2
     * @template A2
     * @param StateMonad<S2,A2> $b
     * @return StateMonad<S2,A2>
     */
    public function then(MonadInstance $b): MonadInstance
    {
        // todo: fix this
        return $this->bind(fn ($s) => $b);
    }
}
