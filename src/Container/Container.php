<?php

namespace thgs\Functional\Container;

use thgs\Functional\Data\Just;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Data\Nothing;

/**
 * Idea here is to have a quick and small inlined container rather than a
 * feature full or extensible one, for now until we know we really need one like
 * that.
 */
class Container
{
    public function __construct(
        /** @var array<string, Instance[]> */
        private array $map = []
    ) {}

    /**
     * @return Maybe<Instance>
     */
    public function getInstance(string $method, mixed $ofType): Maybe
    {
        $typeClassInstances = $this->map[$method] ?? [];
        if (empty($typeClassInstances)) {
            /** @var Maybe<Instance> */
            $return = new Maybe(new Nothing());
            return $return;
        }

        /** @phpstan-assert non-empty-array<Instance> $typeClassInstances */

        foreach ($typeClassInstances as $instance)
                if ($instance->predicate($ofType))
                    return new Maybe(new Just($instance));

        /** @var Maybe<Instance> */
        $return = new Maybe(new Nothing());
        return $return;
    }

    /**
     * @return Maybe<mixed>
     */
    public function invoke(string $method, mixed $ofType, mixed ...$arguments): Maybe
    {
        /**
         * This is :
         * fmap (invokeWithArguments args) (getInstance method ofType)
         */

        $maybeInstance = $this->getInstance($method, $ofType);

        // todo: fix maybe::unwrap or remove it -- its a mapping to nullable type

        $foundInstance = $maybeInstance->getValue();
        if ($foundInstance instanceof Nothing) {
            return new Maybe(new Nothing());
        }

        $instance = $foundInstance->getValue();

        /**
         * @phpstan-assert Instance $instance
         * @var Instance $instance
         * @todo fix this, is this docblock really needed?
         */
        $result = $instance->invoke(...$arguments);
        return new Maybe(new Just($result));
    }

    /**
     * Register a new instance in the container.
     * If the instance has been set before to a definition then it will be re-set.
     * This returns Container however it mutates the same object.
     */
    public function registerInstance(string $method, Instance $instance): Container
    {
        // todo: add type checking for type-class
        $this->map[$method] = $this->addIfNotExists(
            $this->map[$method] ?? [],
            $instance);
        return $this;
    }

    /**
     * @param Instance[] $instances
     * @return Instance[]
     */
    private function addIfNotExists(array $instances, Instance $instance): array
    {
        $replaced = false;
        $newInstances = [];
        foreach ($instances as $current) {
            if ($current->equals($instance)) {
                $newInstances[] = $instance;
                $replaced = true;
                continue;
            }
            $newInstances[] = $current;
        }

        if (!$replaced) {
            $newInstances[] = $instance;
        }
        return $newInstances;
    }
}
