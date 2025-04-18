<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Container\Container;
use thgs\Functional\Container\Instance;
use thgs\Functional\Container\Type;
use thgs\Functional\Data\Just;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Data\Nothing;
use function thgs\Functional\partial;

class ContainerTest extends TestCase
{
    public function testCanRegisterInstance(): void
    {
        $type = new Type(fn ($x) => $x instanceof Maybe, 'Maybe');
        $functorInstanceForMaybe = new Instance($type,
                                                function (\Closure $f, Maybe $fa) {
                                                    $maybe = $fa->getValue();
                                                    if ($maybe instanceof Nothing) {
                                                        return new Maybe(new Nothing());
                                                    }
                                                    $result = partial($f, $maybe->getValue());
                                                    return new Maybe(new Just($result));
                                                });
        $container = new Container();
        $fa = new Maybe(new Just(3));
        $container = $container->registerInstance('fmap', $functorInstanceForMaybe);
        $invokeResult = $container->invoke('fmap', $fa,
                                           // fmap arguments
                                           fn (int $x): int => $x * 3, $fa);

        // invoke result : Maybe<Result>
        $this->assertInstanceOf(Maybe::class, $invokeResult);
        $this->assertInstanceOf(Just::class, $invokeResult->getValue(), 'typeclass not found');

        $fmapResult = $invokeResult->getValue()->getValue();
        // fmap invocation result : fmap (*3) (Just 3)
        $this->assertInstanceOf(Maybe::class, $fmapResult);
        $this->assertInstanceOf(Just::class, $justResult = $fmapResult->getValue());

        // actual result of : 3*3 = 9
        $this->assertEquals(9, $justResult->getValue());
    }
}
