<?php

namespace thgs\Functional;

/**
 * These might grow into more elaborate structures but for now I add
 * them here as draft examples at least.
 */

function yieldFrom(iterable $it): \Generator
{
    yield from $it;
}

function sendFrom(iterable $from, \Generator $receiver): void
{
    foreach ($from as $toSend) {
        $receiver->send($toSend);
    }
}

function makeYieldApply(\Generator $it, \Closure $f): \Closure
{
    return function (...$xs) use ($it, $f): \Generator {
        $value = yield $it;
        yield $f ($value, ...$xs);
    };
}

/**
 * This also could be called `toArray`
 *
 * iterator_to_array is the same and probably more performant?
 */
function consume(iterable $it): array
{
    $result = [];
    foreach ($it as $i) {
        $result[] = $i;
    }
    return $result;
}

/**
 * Is this even useful or makes sense? Seems like identity.
 * This is essentially "take first"
 */
function receiveOne(): \Generator
{
    $value = yield;
    yield $value;
}

function applyRepeater(\Closure $f): \Generator
{
    $value = yield;
    yield $f ($value);
}

function storageReceiver(array &$storage): \Generator
{
    // todo: could return the function as well
    return (function () use (&$storage): \Generator {
        // todo: while true seems necessary otherwise the generator
        // will close, but this could also help to implement a takeWhile
        // but for use with send().
        // a `takeWhile` variation could return the storage in the end?
        while (true) {
            $storage[] = yield;
        }
    })();
}

// todo: variations here can grow too much, including an
// applyStorageReceiverWhile etc.
function applyStorageReceiver(\Closure $f, array &$storage): \Generator
{
    // todo: could return the function as well
    return (function () use ($f, &$storage): \Generator {
        // todo: while true seems necessary otherwise the generator
        // will close, but this could also help to implement a takeWhile
        // but for use with send().
        while (true) {
            $value = yield;
            $storage[] = $f($value);
        }
    })();
}
