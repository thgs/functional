<?php

use PHPUnit\Framework\TestCase;
use thgs\Functional\Control\StateMonad;

use function thgs\Functional\dn;
use function thgs\Functional\partial;
use function thgs\Functional\t;

class StateMonadTest extends TestCase
{
    public function testCanRunState(): void
    {
        /**
         * @var StateMonad<bool,bool>
         */
        $state = StateMonad::state(function (bool $initialState) {
            $newState = $output = true && $initialState;
            return t($output, $newState);
        });

        $result = StateMonad::runState($state, true);
        [$output, $newState] = [$result->fst(), $result->snd()];

        $this->assertTrue($output);
        $this->assertTrue($newState);

        $result = StateMonad::runState($state, false);
        [$output, $newState] = [$result->fst(), $result->snd()];

        $this->assertFalse($output);
        $this->assertFalse($newState);
    }

    public function testCanBind(): void
    {
        $andTrue = StateMonad::state(function (bool $initialState) {
            $newState = $output = true && $initialState;
            return t($output, $newState);
        });
        
        $notTrue = StateMonad::state(function (bool $initialState) {
            $newState = $output = !$initialState;
            return t($output, $newState);
        });

        // todo: fix this bind usage.
        $effectivelyXorFalse = $andTrue->bind(
            fn ($x) => StateMonad::state( fn () => StateMonad::runState($notTrue, $x) )
        );

        $result = StateMonad::runState($effectivelyXorFalse, true);
        [$output, $newState] = [$result->fst(), $result->snd()];
        //var_dump(gettype($output), gettype($newState));

        $this->assertFalse($output);
        $this->assertFalse($newState);

        $result = StateMonad::runState($effectivelyXorFalse, false);
        [$output, $newState] = [$result->fst(), $result->snd()];
        $this->assertTrue($output);
        $this->assertTrue($newState);
    }
}
