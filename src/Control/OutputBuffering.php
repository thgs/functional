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
        $current = $this;
        $action = function () use ($f, $current) {
            $result = ($current)();
            $result_a_to_mb = $f($result->snd())
                // like unIO, we extact the return value of `m b`
                ->__invoke();

            // todo: we need to print again? alternative is to access the IO action from inside `mb` instead of
            // invoking the whole `mb`.
            print $result_a_to_mb->fst();
            return $result_a_to_mb->snd();
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
