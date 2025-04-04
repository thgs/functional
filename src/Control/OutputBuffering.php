<?php

namespace thgs\Functional\Control;

use thgs\Functional\Data\Tuple;
use function thgs\Functional\t;

/**
 * @template A
 */
class OutputBuffering
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
}
