<?php

function closure_(\Closure $f) {
    $f('abc');
}

function callable_(callable $f) {
    $f('abc');
}

class ClosureBench
{
    /**
     * @Iterations(20)
     * @Revs(100)
     */
    public function benchCallable()
    {
        callable_(fn ($x) => $x);
    }

    /**
     * @Iterations(20)
     * @Revs(100)
     */
    public function benchClosure()
    {
        closure_(fn ($x) => $x);
    }

    /**
     * @Iterations(20)
     * @Revs(100)
     */
    public function benchFromPrivateWithCallable()
    {
        callable_(self::class . '::' . 'f');
    }

    /**
     * @Iterations(20)
     * @Revs(100)
     */
    public function benchFromCallableWithClosure()
    {
        closure_(\Closure::fromCallable([self::class, 'f']));
    }

    /**
     * @Iterations(20)
     * @Revs(100)
     */
    public function benchFromCallableWithClosureWithStatic()
    {
        static $callable;
        if (!$callable) {
            $callable = \Closure::fromCallable([self::class, 'f']);
        }
        closure_($callable);
    }

    public static function f($x)
    {
        return $x;
    }
}
