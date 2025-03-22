<?php

function applyReceiverGoto(\Closure $f): \Generator
{
    start: $value = yield;
    yield $f ($value);
    goto start;
}

function applyReceiverWhile(\Closure $f): \Generator
{
    while (true) {
        $value = yield;
        yield $f ($value);
    }
}

class GeneratorGotoBench
{
    /**
     * @Iterations(10)
     * @Revs(5000)
     */
    public function benchGoto500()
    {
        $receiver = applyReceiverGoto(bin2hex(...));
        $processed = [];
        foreach (range(1, 500) as $i) {
            $processed[] = $receiver->send($i);
            $receiver->next();
        }
    }

    /**
     * @Iterations(10)
     * @Revs(5000)
     */
    public function benchWhile500()
    {
        $receiver = applyReceiverWhile(bin2hex(...));
        $processed = [];
        foreach (range(1, 500) as $i) {
            $processed[] = $receiver->send($i);
            $receiver->next();
        }
    }

    /**
     * @Iterations(10)
     * @Revs(5000)
     */
    public function benchGoto5000()
    {
        $receiver = applyReceiverGoto(bin2hex(...));
        $processed = [];
        foreach (range(1, 5000) as $i) {
            $processed[] = $receiver->send($i);
            $receiver->next();
        }
    }

    /**
     * @Iterations(10)
     * @Revs(5000)
     */
    public function benchWhile5000()
    {
        $receiver = applyReceiverWhile(bin2hex(...));
        $processed = [];
        foreach (range(1, 5000) as $i) {
            $processed[] = $receiver->send($i);
            $receiver->next();
        }
    }
}
