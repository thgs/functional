<?php

use PHPUnit\Framework\TestCase;

use function thgs\Functional\applyReceiver;
use function thgs\Functional\consume;
use function thgs\Functional\sendFrom;
use function thgs\Functional\storageReceiver;
use function thgs\Functional\applyStorageReceiver;
use function thgs\Functional\yieldFrom;

class IterationFunctionsTest extends TestCase
{
    // todo: test other candidates of `iterable` etc

    public function testCanYieldFrom(): void
    {
        $gen = yieldFrom((function () {
            yield 1;
            yield 2;
            yield 3;
        })());

        $results = [];
        foreach ($gen as $e) {
            $results[] = $e;
        }

        $this->assertEquals([1,2,3], $results);
    }

    public function testCanConsume(): void
    {
        $gen = yieldFrom((function () {
            yield 1;
            yield 2;
            yield 3;
        })());

        $this->assertEquals([1,2,3], consume($gen));
    }

    public function testStorageReceiver(): void
    {
        $storage = [];
        $receiver = storageReceiver($storage);

        $receiver->send(1);
        $this->assertTrue($receiver->valid());
        $receiver->send(1);
        $this->assertTrue($receiver->valid());
        $receiver->send(1);

        $this->assertEquals([1,1,1], $storage);
    }

    public function testCanSend(): void
    {
        $storage = [];
        $receiver = storageReceiver($storage);

        sendFrom(range(1,3), $receiver);

        $this->assertEquals(range(1,3), $storage);
    }

    public function testCanReceiveAndStore(): void
    {
        $storage = [];
        $receiver = applyStorageReceiver(bin2hex(...), $storage);
        sendFrom(range(1,3), $receiver);
        $this->assertEquals(['31', '32', '33'], $storage);
    }

    public function testCanReceiveAndApply(): void
    {
        $receiver = applyReceiver(bin2hex(...));
        $processed = [];
        foreach (range(1, 5) as $i) {
            $processed[] = $receiver->send($i);
            $receiver->next();
        }

        $this->assertEquals(['31', '32', '33', '34', '35'], $processed);
    }
}
