<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Container\Method;
use thgs\Functional\Container\MethodContainer;
use thgs\Functional\Container\Type;
use thgs\Functional\Data\Just;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Data\Nothing;
use function thgs\Functional\partial;

class MethodContainerTest extends TestCase
{
    public function testCanRegisterInstance(): void
    {
        $type = new Type(fn ($x) => $x instanceof Maybe, 'Maybe');
        $fmapForMaybe = new Method('fmap', $type,
                                                function (\Closure $f, Maybe $fa) {
                                                    $maybe = $fa->getValue();
                                                    if ($maybe instanceof Nothing) {
                                                        return new Maybe(new Nothing());
                                                    }
                                                    $result = partial($f, $maybe->getValue());
                                                    return new Maybe(new Just($result));
                                                });
        $container = new MethodContainer();
        $fa = new Maybe(new Just(3));
        $container = $container->registerMethod($fmapForMaybe);
        $invokeResult = $container->invoke('fmap', $fa,
                                           // fmap arguments
                                           fn (int $x): int => $x * 3, $fa);

        // invoke result : Maybe<Result>
        $this->assertInstanceOf(Maybe::class, $invokeResult);
        $this->assertInstanceOf(Just::class, $invokeResult->getValue(), 'method not found');

        $fmapResult = $invokeResult->getValue()->getValue();
        // fmap invocation result : fmap (*3) (Just 3)
        $this->assertInstanceOf(Maybe::class, $fmapResult);
        $this->assertInstanceOf(Just::class, $justResult = $fmapResult->getValue());

        // actual result of : 3*3 = 9
        $this->assertEquals(9, $justResult->getValue());
    }

    public function testCanOverrideRegisteredInstance(): void
    {
        $intOrFloatType = new Type(fn ($x) => is_int($x) || is_float($x), 'int|float');
        $looseEq = new Method('equals', $intOrFloatType, fn (int|float $x, int|float $y) => ((int) $x) == ((int) $y));
        $strictEq = new Method('equals', $intOrFloatType, fn ($x, $y) => $x === $y);

        $container = new MethodContainer();
        $container = $container->registerMethod($looseEq);
        $container = $container->registerMethod($strictEq);

        $x = 1;
        $y = 1.0;

        $invokeResult = $container->invoke('equals', $x,
                                     // equals arguments
                                     $x, $y);

        // invoke result : Maybe<Result>
        $this->assertInstanceOf(Maybe::class, $invokeResult);
        $this->assertInstanceOf(Just::class, $invokeResult->getValue(), 'method not found');

        $equalsResult = $invokeResult->getValue()->getValue();

        $this->assertFalse($equalsResult);
    }
}
