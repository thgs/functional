<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Data\IO;
use thgs\Functional\Proof\FunctorProof;

class IOTest extends TestCase
{
    use FunctorProof;
    
    public function testCanReturnValueAfterIO(): void
    {
        $io = new IO(function () { echo '123'; return 5; });

        /**
         * Output buffering is really not mandatory for this test.  We assume
         * there is an IO action in the closure, it does not really have to be,
         * neither we care if there is or not. Nevertheless we can assert that
         * "something" happened and then we got a result.
         */
        ob_start();
        $result = $io->getValue();
        $output = ob_get_clean();

        $this->assertEquals(5, $result);
        $this->assertEquals('123', $output);
    }

    public function testCanFmap(): void
    {
        $data = new IO(function () { /* action would be here */ return 5; });
        $mapped = $data->fmap(fn ($x) => $x * 2);

        $result = $mapped->getValue();
        $this->assertEquals(10, $result);
    }

    public function testIsAFunctor(): void
    {
        $this->assertInstanceIsFunctor(
            new IO(function () { /* action would be here */ return 5; }),
            fn (int $x): bool => $x == 5,
            fn (bool $x): string => $x == true ? '100' : '500'
        );
    }
}
