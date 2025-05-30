<?php

namespace thgs\Functional\Control;

use thgs\Functional\Control\Typeclass\ApplicativeInstance;
use thgs\Functional\Control\Typeclass\MonadInstance;
use thgs\Functional\Typeclass\FunctorInstance;

use function thgs\Functional\partial;


/**
 * This gives a context of IO for anything deemed as such. It is up to
 * the user to decide what should be treated as a side-effect and what
 * should not. This should probably be called "Sideeffect" as we do
 * not implement anything for the global state or fantasy land (if the
 * term is correct).
 *
 * @template ReturnType
 *
 * Here we care more for the result of that action, rather than the action itself.
 * So for now the template is the result.
 * @todo phpstan complains a lot.
 *
 * @implements FunctorInstance<ReturnType>
 * @implements ApplicativeInstance<ReturnType>
 * @implements MonadInstance<ReturnType>
 */
class IO implements
    FunctorInstance,
    ApplicativeInstance,
    MonadInstance
{
    public function __construct(
        /**
         * @var \Closure():ReturnType
         */
        private \Closure $action
    ) {}

    /**
     * Just a convenience static constructor.
     * @return IO<null>
     */
    public static function unit(): self
    {
        return new self(fn () => null);
    }

    /**
     * This is effectively (<-)
     * @return ReturnType
     */
    public function getValue(): mixed
    {
        return ($this->action)();
    }

    /**
     * @return ReturnType
     */
    public function __invoke(mixed ...$xs): mixed
    {
        return ($this->action)(...$xs);
    }

    /*
     * todo: For the annotation, older code uses (see Maybe::fmap):
     *       return FunctorInstance<B1>
     * which one is better? IO<B1> or FunctorInstance<B1>
     * Should be IO since after they fmap there may be other things they do.
     */

    /**
     * @template B1
     * @param \Closure(ReturnType):B1 $f
     * @return IO<B1>
     */
    public function fmap(\Closure $f): FunctorInstance
    {
        /**
         * This should not run the action.
         */

        /**
         * @todo How could use a Composition ?
         * Wip with Composition
         * var Composition<R,B1,IO<B1>>
        $composition = new Composition( fn () => ($this->action)() );
        $composition->fmap($f);
        return new IO($composition);
         */

        // todo: self or static?
        return new self(
            function () use ($f) {
                $result = ($this->action)();
                return $f($result);
            }
        );
    }

    /**
     * @template X
     * @param X $a
     * @return IO<X>
     */
    public static function pure($a): ApplicativeInstance
    {
        // constructor will throw typeError if it is not callable.
        return self::inject($a);
    }

    /**
     * (<*>) :: f (a -> b) -> f a -> f b
     * <*> is always implemented in reverse with what the others are
     * so this instance is actually the `f ( a -> b)` instead of `f a`.
     * Is that correct/usable? How you sequence more than one?
     */
    public function sequence(ApplicativeInstance $fa): ApplicativeInstance
    {
        // runtime type-check
        if (!$fa instanceof IO) {
            throw new \TypeError('Expected instance of IO');
        }

        // todo: rewrite this in a more concise manner, but for brevity now:
        $do = function () use ($fa) {
            $f = $this();
            $g = $fa();
            return partial ($f) ($g);
        };
        return IO::inject($do);
    }

    /**
     * @template R
     * @param R|\Closure():R $a
     * @return IO<R>
     */
    public static function inject($a): MonadInstance
    {
        if ($a instanceof \Closure) {
            /**
             * @todo here we override static analysis, given closure might not be Closure():R
             * @var \Closure():R $a
             */
            return new self(fn () => $a());
        }

        return new self(fn () => $a);
    }

    /**
     * (>>=) :: m a -> (a -> m b) -> m b
     *
     * bindIO :: IO a -> (a -> IO b) -> IO b
     * bindIO (IO m) k = IO (\ s -> case m s of (# new_s, a #) -> unIO (k a) new_s)
     *
     * @template B
     * @param \Closure(ReturnType):IO<B> $f
     * @return IO<B>
     */
    public function bind(\Closure $f): MonadInstance
    {
        $action = $this->action;
        $do = function () use ($f, $action) {
            $x = ($action)();

            return partial($f, $x)
                ->getValue(); // unIO
        };
        return new IO($do);
    }

    public function then(MonadInstance $b): MonadInstance
    {
        /**
         * Haskell's implementation is:
         *
         * -- | Sequentially compose two actions, discarding any value produced
         * -- by the first, like sequencing operators (such as the semicolon)
         * -- in imperative languages.
         * (>>)        :: forall a b. m a -> m b -> m b
         * m >> k = m >>= \_ -> k -- See Note [Recursive bindings for Applicative/Monad]
         * {-# INLINE (>>) #-}
         */

        return $this->bind(fn () => $b);
    }
}
