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
     * @Iterations(10)
     */
    public function benchCallable()
    {
        callable_(fn ($x) => $x);
    }

    /**
     * @Iterations(10)
     */
    public function benchClosure()
    {
        closure_(fn ($x) => $x);
    }

    /**
     * @Iterations(10)
     */
    public function benchFromPrivateWithCallable()
    {
        callable_(self::f(...));
    }

    /**
     * @Iterations(10)
     */
    public function benchFromCallableWithClosure()
    {
        closure_(\Closure::fromCallable(self::f(...)));
    }

    private static function f($x)
    {
        return $x;
    }
}
