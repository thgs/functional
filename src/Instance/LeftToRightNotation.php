<?php

namespace thgs\Functional\Instance;

use thgs\Functional\Typeclass\CategoryInstance;
use thgs\Functional\Typeclass\NotationInstance;
use function PHPStan\dumpType;

/**
 * @template Elements
 * @implements NotationInstance<Elements>
 *
 * @todo instead of NotationInstance we could define one generic
 * Notation? I think the way the order is stops us from this and since
 * it is not factored in yet we need to split to interface and order
 * implementations.
 */
class LeftToRightNotation implements
    NotationInstance
{
    public function __construct(
        /**
         * @var CategoryInstance<Elements>
         */
        private CategoryInstance $category
    ) {}

    /**
     * @todo could pass first argument the categoryInstance, also, do
     * we need an instance of the category?
     */
    public function composeMany(mixed ...$elements): mixed
    {
        $current = array_shift($elements);
        if (!$current) {
            // probably unreachable?
            throw new \Exception('Need at least one parameter');
        }

        while ($new = array_shift($elements)) {
            // todo: not sure why phpstan complains here
            $current = $this->category->compose($current, $new);
        }
        return $current;
    }
}
