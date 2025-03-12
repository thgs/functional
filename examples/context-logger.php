<?php

require_once \dirname(__DIR__) . '/vendor/autoload.php';

use thgs\Functional\Data\Tuple;
use thgs\Functional\Typeclass\Adapter\CallableWiringFunctorAdapter;
use thgs\Functional\Typeclass\FunctorInstance;
use thgs\Functional\Wrapper\CallbackWrapper;
use thgs\Functional\Wrapper\Wrapper;
use function thgs\Functional\c;
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

// CallableWiringFunctorAdapter code -------------------------------
// @todo sort the below
// this does not look like the write name or idea conceptually
// Some better understanding of fmap and functors is needed here.
// Feels like a misalignment.

/**
 * We create a logger that is a functor and can fmap by passing a "wiring" fn,
 * conveniently this is a closure carrying the dependencies (Psr3Logger).
 */
$logger = new CallableWiringFunctorAdapter( fn (Tuple $p) => $psr3Logger->log($p->fst(), $p->snd()) );

/**
 * We start mapping functions over our functor.  The below will add prefix and
 * extraContext.
 */
// todo: fix this as it is contramap and not fmap really - I think.
$contextLogger = $logger ->fmap ( fn (Tuple $p): Tuple => t ($prefix . $p->fst(), [$extraContext] + $p->snd() ) );

/**
 * We call the logger, (only one method; log() as per wiring) and actually log things
 */
$contextLogger (t ("one",   ["context"]) );
$contextLogger (t ("two",   ["context"]) );
$contextLogger (t ("three", ["context"]) );
$contextLogger (t ("four",  ["context"]) );


// CallbackWrapper code -------------------------------

/**
 * Create a CallbackWrapper that connects the psr3Logger to a Tuple input.
 * However here we can also pass the `fmap` method. This is a POC for wrapper/inject
 */
$wrapper = new CallbackWrapper(
    fn (Tuple $p) => $psr3Logger->log($p->fst(), $p->snd()),
    ['fmap' => fn (callable $f) => c ($f) ->fmap ($this->wiring)]
);

/**
 * We create the same contextLogger
 */
$contextLogger = $wrapper ->fmap ( fn (Tuple $p): Tuple => t ($prefix . $p->fst(), [$extraContext] + $p->snd() ) );

($contextLogger) (t ("wrapped one", ["contextt"]));
($contextLogger) (t ("wrapped two", ["contextt"]));


// Wrapper code ------------------------------------

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
$wrapper = new Wrapper(fn (Tuple $p) => $psr3Logger->log($p->fst(), $p->snd()));

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


exit();
// -------------------------------------------------

// Below fails
// todo: what if in the above line that now passes callable(Tuple):Tuple, we fmap with callable(Tuple):Int

$mapFunction = function (Tuple $p): Int {
    return strlen($p->fst());
};
$addedFunctionality = $contextLogger -> fmap ( $mapFunction );

$return = $addedFunctionality (t ("added", ["additional"]));
var_dump($return);
exit();


// Dreamland code --------------------------------
// possibly better interfaces?

$wrapper = CallbackWrapper::with(fn (Tuple $p) => $psr3Logger->log($p->fst(), $p->snd()));
Functor::add($wrapper);

// or
$wrapper = CallbackWrapper::with(
    fn (Tuple $p) => $psr3Logger->log($p->fst(), $p->snd()),
    [FunctorInstance::class]
);

// or with custom
$wrapper = CallbackWrapper::with(
    fn (Tuple $p) => $psr3Logger->log($p->fst(), $p->snd()),
    ['fmap' => fn (callable $f) => c ($f) ->fmap ($this->wiring) ]
);

