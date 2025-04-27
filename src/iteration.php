<?php

namespace thgs\Functional;

/**
 * These might grow into more elaborate structures but for now I add
 * them here as draft examples at least.
 */

/**
 * @template A
 * @param iterable<A> $it
 * @return \Generator<A>
 * @todo is there no PHP function for this?
 */
function yieldFrom(iterable $it): \Generator
{
    yield from $it;
}

/**
 * This essentially defines an interaction between two generators
 * using send. Effectively will consume the $from generator.
 *
 * @template A
 * @template GenKey
 * @template GenValue
 * @template GenReturn
 *
 * @param iterable<A> $from
 * @param \Generator<GenKey,GenValue,A,GenReturn> $receiver
 */
function sendFrom(iterable $from, \Generator $receiver): void
{
    foreach ($from as $toSend) {
        $receiver->send($toSend);
    }
}

/**
 * This also could be called `toArray`
 *
 * iterator_to_array is the same and probably more performant?
 *
 * @template A
 * @param iterable<A> $it
 * @return array<A>
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

function applyReceiver(\Closure $f): \Generator
{
    /**
     * Not sure why phpstan does not like while(true).
     * @phpstan-ignore while.alwaysTrue
     */
   while (true) {
        $value = yield;
        yield $f ($value);
    }
}

/**
 * @template A
 * @param array<A> $storage
 * @return \Generator<int,null,A,never>
 */
function storageReceiver(array &$storage): \Generator
{
    // todo: could return the function as well
    // todo: while true seems necessary otherwise the generator
    // will close, but this could also help to implement a takeWhile
    // but for use with send().
    // a `takeWhile` variation could return the storage in the end?

    /**
     * Not sure why phpstan does not like while(true).
     * @phpstan-ignore while.alwaysTrue
     */
    while (true) {
        $storage[] = yield;
    }
}

/**
 * @todo variations here can grow too much, including an
 * applyStorageReceiverWhile etc.
 *
 * @template A
 * @template B
 *
 * @param \Closure(A):B $f
 * @param array<B> &$storage
 * @return \Generator<int,null,A,never> $receiver
 */
function applyStorageReceiver(\Closure $f, array &$storage): \Generator
{
    // todo: could return the function as well
    // todo: while true seems necessary otherwise the generator
    // will close, but this could also help to implement a takeWhile
    // but for use with send().

    /**
     * Not sure why phpstan does not like while(true).
     * @phpstan-ignore while.alwaysTrue
     */
    while (true) {
        $value = yield;
        $storage[] = $f($value);
    }
}
