<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Typeclass\Semigroup;
use function thgs\Functional\assoc;
use function thgs\Functional\just;

class SemigroupTest extends TestCase
{
    public function testCanReturnRegisteredAssoc(): void
    {
        Semigroup::register(
            is_string(...),
            instanceName: 'Concat',
            assoc: fn ($a, $b) => $a . $b,
            sconcat: fn (iterable $strings) => implode('', iterator_to_array($strings))
        );

        $this->assertEquals("Hello world!", assoc("Hello", " world!"));
    }

    public function testCanAssocWithSemigroupInstance(): void
    {
        $maybe1 = just('1');
        $maybe2 = just('2');

        // @todo here reusing the previous test's container state

        $this->assertEquals("12", assoc($maybe1, $maybe2)->getValue()->getValue());
    }

    public function testWillThrowOnUnknownInstanceInAssoc(): void
    {
        $this->expectException(\TypeError::class);
        assoc(12.0, 81.0);
    }
}
