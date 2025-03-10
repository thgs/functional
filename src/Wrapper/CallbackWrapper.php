<?php

namespace thgs\Functional\Wrapper;

use thgs\Functional\Expression\Composition;
use function thgs\Functional\partial;

/**
 * @template I
 * @template O
 */
class CallbackWrapper
{
    private \Closure $wiring;

    /** @var array<string, callable> */
    private array $injectedMethods = [];

    public function __construct(callable $wiring, array $injections)
    {
        if ($wiring instanceof Composition) {
            $this->wiring = \Closure::fromCallable($wiring);
        } elseif ($wiring instanceof \Closure) {
            $this->wiring = $wiring->bindTo($this, $this);
        } else {
            $this->wiring = \Closure::fromCallable($wiring)->bindTo($this, $this);
        }

        foreach ($injections as $method => $callable)
            $this->injectedMethods[$method] =
                ($callable instanceof \Closure ? $callable : \Closure::fromCallable($callable))->bindTo($this, $this);
    }

    /**
     * @return O
     */
    public function __invoke(): mixed
    {
        /**
         * We know it is callable and partial will just wrap it can
         * fix the types later because partial accepts many things at
         * one place and phpstan does not know which one to pick
         * @var callable $partial
         */
        $partial = partial ($this->wiring);
        return $partial (...func_get_args());
    }

    public function __call(string $name, array $arguments)
    {
        $foundClosure = $this->injectedMethods[$name] ?? null;
        if (!$foundClosure)
            throw new \Exception('method not found: ' . $name);

        return new self(($foundClosure) (...$arguments), $this->injectedMethods);

        /**
         * This is a POC because with this the types have gone out of
         * the window.
         */
    }
}
