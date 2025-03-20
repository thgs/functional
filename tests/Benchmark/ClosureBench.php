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
        $i = 1000;
        while ($i--)
            callable_(fn ($x) => $x);
    }

    /**
     * @Iterations(20)
     * @Revs(100)
     */
    public function benchClosure()
    {
        $i = 1000;
        while ($i--)
            closure_(fn ($x) => $x);
    }

    /**
     * @Iterations(20)
     * @Revs(100)
     */
    public function benchFromPrivateWithCallable()
    {
        $i = 1000;
        while ($i--)
            callable_(self::class . '::' . 'f');
    }

    /**
     * @Iterations(20)
     * @Revs(100)
     */
    public function benchFromCallableWithClosure()
    {
        $i = 1000;
        while ($i--)
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
        $i = 1000;
        while ($i--)
            closure_($callable);
    }

    public static function f($x)
    {
        return $x;
    }
}
