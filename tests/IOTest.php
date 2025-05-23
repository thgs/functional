<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Control\IO;
use thgs\Functional\Control\Typeclass\ApplicativeInstance;
use thgs\Functional\Data\Maybe;
use thgs\Functional\Testing\FunctorLawsAssertions;
use thgs\Functional\Typeclass\FunctorInstance;

class IOTest extends TestCase
{
    use FunctorLawsAssertions;
    
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
        /** @var IO<Int> */
        $data = new IO(function () { /* action would be here */ return 5; });

        /**
         * fmap (*2) $data
         * Essentially we fmap a `Int -> Int` to an `IO Int` functor
         * @var FunctorInstance<IO<Int>>
         */
        $mapped = $data->fmap(fn ($x) => $x * 2);

        /**
         * $result <- $mapped
         */
        $result = $mapped();
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

    public function testCanConstructApplicativeWithPure(): void
    {
        $applicative = IO::pure(fn () => 123);

        $this->assertInstanceOf(ApplicativeInstance::class, $applicative);
        $this->assertInstanceOf(IO::class, $applicative);
        $this->assertEquals(123, $applicative->getValue());
    }

    public function testCanSequenceApplicatives(): void
    {
        /** @var array */
        $sideEffectVar = [];

        // This must be IO (a -> b)
        $ap1 = new IO(function () use (&$sideEffectVar) {
            /** assumed IO happening here */
            $sideEffectVar['ap1'] = 123;

            /** (a -> b) */
            return fn($x) => $x+3;
        });

        // This must be IO a
        $ap2 = new IO(function () use (&$sideEffectVar) {
            /** assumed IO happening here */
            $sideEffectVar['ap2'] = 456;

            return 4;
        });


        // IO (a -> b)  <*>  IO a   :: IO b
        $result = $ap1->sequence($ap2);
        $this->assertInstanceOf(ApplicativeInstance::class, $result);
        $this->assertEmpty($sideEffectVar);

        $this->assertEquals(7, $result());
        $this->assertEquals(['ap1' => 123, 'ap2' => 456], $sideEffectVar);
    }

    /**
     * Not yet fully sure if this should be enforced, by types it probably should.
     */
    public function testThrowsWhenSequenceToAnotherInstanceOfApplicative(): void
    {
        /** @var array */
        $sideEffectVar = [];

        // This must be IO (a -> b)
        $ap1 = new IO(function () {
            /** assumed IO happening here */

            /** (a -> b) */
            return fn($x) => $x+3;
        });

        // This must be IO a
        $ap2 = Maybe::pure(3);

        $this->expectException(TypeError::class);

        // IO (a -> b)  <*>  IO a   :: IO b
        $result = $ap1->sequence($ap2);
    }

    public function testCanBind(): void
    {
        // $ioString :: IO String
        $ioString = IO::inject("Hello");

        // $f :: (String -> IO LowercaseString)
        $sideEffectCheck = [];
        $f = function (string $x) use (&$sideEffectCheck) {
            $sideEffectCheck[] = 'called';

            return IO::inject(strtolower($x));
        };

        // $bound :: IO LowercaseString
        $bound = $ioString ->bind ($f);

        $this->assertInstanceOf(IO::class, $bound);
        $this->assertEmpty($sideEffectCheck);

        // Perform IO
        $result = $bound();

        $this->assertEquals('hello', $result);
        $this->assertEquals(['called'], $sideEffectCheck);
    }

    public function testCanBindMultiple(): void
    {
        // todo: in reality IO probably happens inside IO::inject and not before, or both?
        $int =
            IO::inject('Hello')
            ->bind(fn (string $x) => /* IO happens here */ IO::inject(strtolower($x)))
            ->bind(fn (string $x) => /* IO happens here */ IO::inject(strtoupper($x)))
            ->bind(fn (string $x) => /* IO happens here */ IO::inject(strlen($x)))
            ->getValue();

        $this->assertEquals(5, $int);
        $this->assertIsInt($int);
    }

    public function testCanBindWithDoNotation(): void
    {
        $int = thgs\Functional\dn(
            IO::inject('Hello'),
            fn (string $x) => /* IO happens here */ IO::inject(strtolower($x)),
            fn (string $x) => /* IO happens here */ IO::inject(strtoupper($x)),
            fn (string $x) => /* IO happens here */ IO::inject(strlen($x)),
        )
            ->getValue();

        /**
         * @todo Should/can this getValue() be inside dn() ?  IO (and
         * each other) could provide ioDo(IO ...$ios) that in the end
         * does getValue() or whatever it is on each one so as not to
         * have a unified interface for unwrapping.  But we could as
         * well have.
         */

        $this->assertEquals(5, $int);
        $this->assertIsInt($int);
    }

    public function testCanReturnIOUnit(): void
    {
        $this->assertNull(IO::unit()());
    }

    public function testCanSequenceWithThen(): void
    {
        $sideEffect = 0;
        $io1 = IO::inject(function () use (&$sideEffect) {
            $sideEffect++;
        });
        $io2 = IO::inject(function () use (&$sideEffect) {
            $sideEffect++;
        });

        $io3 = $io1->then($io2);
        $this->assertEquals(0, $sideEffect);

        ($io3)();

        $this->assertEquals(2, $sideEffect);
    }
}
