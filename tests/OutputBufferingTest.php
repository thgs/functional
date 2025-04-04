<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Control\IO;
use thgs\Functional\Control\OutputBuffering;

class OutputBufferingTest extends TestCase
{
    public function testCanReturnOutputAsString(): void
    {
        $action = function () {
            print "123\n";
            return 4;
        };
        $ob = new OutputBuffering(new IO($action));
        $result = $ob();
        
        $this->assertEquals("123\n", $result->fst());
        $this->assertEquals(4, $result->snd());
    }

    public function testCanReturnIOResultWhenVoid(): void
    {
        $action = function () {
            print "123\n";
        };
        $ob = new OutputBuffering(new IO($action));
        $result = $ob();
        
        $this->assertEquals("123\n", $result->fst());
        $this->assertEquals(null, $result->snd());
    }

    public function testCanOperateNested(): void
    {
        ob_start();
        $action = function () {
            print "123\n";
        };
        $ob = new OutputBuffering(new IO($action));
        $result = $ob();
        
        $this->assertEquals("123\n", $result->fst());
        $this->assertEquals(null, $result->snd());
        $this->assertFalse(false, ob_get_clean());
    }

    public function testWillBubbleException(): void
    {
        $previousLevel = ob_get_level();
        $action = function () {
            print "123\n";
            throw new \Exception('Something threw an exception');
        };
        $ob = new OutputBuffering(new IO($action));

        $this->expectException(\Exception::class);
        $ob();

        $this->assertEquals($previousLevel, ob_get_level());
        // todo: why this is a risky test?
    }
}
