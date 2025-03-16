<?php

require_once \dirname(__DIR__) . '/vendor/autoload.php';

use thgs\Functional\Data\Tuple;
use thgs\Functional\Wrapper\Wrapper;
use function thgs\Functional\t;

/**
 * This is a rough and simplified recreation of https://github.com/WyriHaximus/php-psr-3-context-logger
 * to see how well we fare trying to make a functor out of Psr3LoggerInterface (or similar)
 */

// Background initialisation -----------------

$psr3Logger = new class /*implements LoggerInterface -- level removed to use Tuple instead of Tuple3 */
{
    public function log($message, array $context = []): void
    {
        print "thgs.log: " . $message . " [" . implode(',', $context) ."]\n";
    }
};

$prefix = 'functional: ';
$extraContext = 'contextual';

/**
 * We use the new Contravariant Wrapper and pass it the wiring as we did before.
 * We still need a Tuple right away to bundle multiple things in one.
 *
 * todo: To support returning array though, we would have to unpack it with `...`
 * when we call the wrapped function.
 * ie it would be new Wrapper(fn ($first, $second) => $psr3Logger($first, $second));
 * but we would have to be able somehow to make the distinction between:
 * - this is an array
 * - this is an array of parameters that you need to unpack.
 */
$wrapper = Wrapper::withAdjustedInput(fn (Tuple $p) => $psr3Logger->log($p->fst(), $p->snd()));

/**
 * Using `contramap` we do not have to define the obvious
 * `fmap` function we passed before. This will do for any class as it
 * adjusts the input. Another way to see it is that this function we
 * pass here is the guide on how to go from the new input to the old
 * input. So we can plug it together (this is how it works).
 */
$contextLogger = $wrapper-> contramap (fn (Tuple $p): Tuple => t ($prefix . $p->fst(), [$extraContext] + $p->snd()) );

/**
 * Test it a little
 */
($contextLogger) (t ("contravariant one", ["contextt"]));
($contextLogger) (t ("contravariant two", ["contextt"]));

/**
 * Now let's change the output, which in this case was void
 */

$changedOutputLogger = $contextLogger-> fmap (fn () => time());

$currentTime = ($changedOutputLogger) (t ("This will return time!", ["timed","timed!"]));
print "Returned : $currentTime \n";
$currentTime = ($changedOutputLogger) (t ("This will return time!", ["timed"]));
print "Returned : $currentTime \n";


// todo: add example to fmap : strlen $p->fst()
