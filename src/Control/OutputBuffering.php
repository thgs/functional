<?php

namespace thgs\Functional\Control;

use Closure;
use thgs\Functional\Control\Typeclass\MonadInstance;
use thgs\Functional\Data\Tuple;
use function thgs\Functional\t;

/**
 * @template A
 * @implements MonadInstance<OutputBuffering<A>>
 */
class OutputBuffering implements
    MonadInstance
{
    public function __construct(
        /** @var IO<A> */
        private IO $io
    ) {}

    /**
     * @return Tuple<string,A>
     */
    public function __invoke(mixed ...$xs): Tuple
    {
        ob_start();
        $ioResult = ($this->io)(...$xs);
        $output = ob_get_clean();
        if ($output === false) {
            throw new \LogicException('Output buffering was inactive');
        }
        return t($output, $ioResult);
    }

    /**
     * @template A1
     * @param A1 $a
     * @return OutputBuffering<A1>
     */
    public static function inject($a): MonadInstance
    {
        return new self(IO::inject($a));
    }

    /**
     * @template B
     * @param \Closure(A):OutputBuffering<B> $f
     * @return OutputBuffering<B>
     */
    public function bind(Closure $f): MonadInstance
    {
        $action = function () use ($f) {
            $currentIoResult = ($this->io)(); // todo: support ...$xs ?

            $mb = $f($currentIoResult);
            /**
             * This is not guaranteed and not checked anywhere else
             * really.  We could let it up to static analysis to error
             * out when you pass an incorrect Closure. Not entirely
             * sure if we should check though.
             *
             * @phpstan-ignore instanceof.alwaysTrue
             */
            if (!$mb instanceof OutputBuffering) {
                throw new \TypeError('Bound closure does not return instance of ' . self::class);
            }

            return ($mb->io)();
        };
        return new self(new IO($action));
    }

    /**
     * @template B1
     * @param OutputBuffering<B1> $b
     * @return OutputBuffering<B1>
     */
    public function then(MonadInstance $b): MonadInstance
    {
        return $this->bind(fn () => $b);
    }
}
