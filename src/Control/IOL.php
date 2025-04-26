<?php

namespace thgs\Functional\Control;

use thgs\Functional\Control\Typeclass\MonadInstance;
use thgs\Functional\Typeclass\FunctorInstance;

use function thgs\Functional\partial;

/**
 * This is a draft IO Linear implementation where you can define the multiplicity.
 *
 * @todo the "linearity" could be independent of IO really
 *
 * @template ReturnType
 * @template Multiplicity of ?int
 *
 * @implements FunctorInstance<ReturnType>
 * @implements MonadInstance<ReturnType>
 */
class IOL implements
    FunctorInstance,
    MonadInstance
{
    private ?int $remaining;

    public function __construct(
        /**
         * @var \Closure():ReturnType
         */
        private \Closure $action,
        /**
         * Passing null here means "unrestricted" multiplicity.
         * @var Multiplicity
         */
        private ?int $multiplicity
    ) {
        $this->remaining = $multiplicity;
    }

    private function called(): void
    {
        if ($this->remaining === null) {
            return;
        }

        $this->remaining--;
        if ($this->remaining < 1) {
            throw new \Exception('Unable to call');
        }
    }

    /**
     * @return ReturnType
     */
    public function getValue(): mixed
    {
        $this->called();
        return ($this->action)();
    }

    /**
     * @return ReturnType
     */
    public function __invoke(mixed ...$xs): mixed
    {
        $this->called();
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
     * @return IOL<B1, Multiplicity>
     */
    public function fmap(\Closure $f): FunctorInstance
    {
        return new self(
            action: function () use ($f) {
                $this->called();
                $result = ($this->action)();
                return $f($result);
            },
            multiplicity: $this->multiplicity
        );
    }

    /**
     * @template R
     * @param R|\Closure():R $a
     * @return IOL<R, int<1,1>>
     */
    public static function inject($a): MonadInstance
    {
        if ($a instanceof \Closure) {
            /**
             * @todo here we override static analysis, given closure might not be Closure():R
             * @var \Closure():R $a
             */
            return new self(fn () => $a(), 1);
        }

        return new self(fn () => $a, 1);
    }

    /**
     * (>>=) :: m a -> (a -> m b) -> m b
     *
     * bindIO :: IO a -> (a -> IO b) -> IO b
     * bindIO (IO m) k = IO (\ s -> case m s of (# new_s, a #) -> unIO (k a) new_s)
     *
     * @template B
     * @param \Closure(ReturnType):IO<B> $f
     * @return IOL<B,Multiplicity>
     */
    public function bind(\Closure $f): MonadInstance
    {
        $action = $this->action;
        $do = function () use ($f, $action) {
            $this->called();
            $x = ($action)();

            // todo: could add a type check here? that return type is indeed m b ?
            return (partial ($f, $x))
                ->getValue(); // unIO
        };
        return new IOL($do, $this->multiplicity);
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
