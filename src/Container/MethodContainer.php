<?php

namespace thgs\Functional\Container;

use thgs\Functional\Data\Maybe;

/**
 * Idea here is to have a quick and small inlined container rather than a
 * feature full or extensible one, for now until we know we really need one like
 * that.
 */
class MethodContainer
{
    public function __construct(
        /** @var array<string, Method[]> */
        private array $map = []
    ) {}

    /**
     * @return Maybe<Method>
     */
    public function getMethodImplementation(string $method, mixed $ofType): Maybe
    {
        $typeClassMethods = $this->map[$method] ?? [];
        if (empty($typeClassMethods)) {
            return Maybe::nothing();
        }

        /** @phpstan-assert non-empty-array<Method> $typeClassInstances */

        if ($ofType instanceof TypeName)
            foreach ($typeClassMethods as $method)
                if ($method->type->name == $ofType->name)
                    return Maybe::just($method);

        foreach ($typeClassMethods as $method)
            if ($method->predicate($ofType))
                return Maybe::just($method);

        return Maybe::nothing();
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

        /**
         * @todo Support multiple $ofType here and in the predicate?
         */

        // todo: inline this for micro-optimisation?

        return $this->getMethodImplementation($method, $ofType)
            ->fmap(
                fn (Method $method) => $method->invoke(...$arguments));
    }

    /**
     * Register a new instance in the container.
     * If the instance has been set before to a definition then it will be re-set.
     * This returns Container however it mutates the same object.
     */
    public function registerMethod(Method $method): self
    {
        // todo: add type checking for type-class
        $this->map[$method->name] = $this->addIfNotExists(
            $this->map[$method->name] ?? [],
            $method);
        return $this;
    }

    /**
     * @param Method[] $methods
     * @return Method[]
     */
    private function addIfNotExists(array $methods, Method $method): array
    {
        $replaced = false;
        $newMethods = [];
        foreach ($methods as $current) {
            if ($current->equals($method)) {
                $newMethods[] = $method;
                $replaced = true;
                continue;
            }
            $newMethods[] = $current;
        }

        if (!$replaced) {
            $newMethods[] = $method;
        }
        return $newMethods;
    }
}
