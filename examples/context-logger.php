<?php

require_once \dirname(__DIR__) . '/vendor/autoload.php';

use thgs\Functional\Data\Tuple;
use thgs\Functional\Typeclass\Adapter\CallableWiringFunctorAdapter;
use thgs\Functional\Typeclass\FunctorInstance;
use thgs\Functional\Wrapper\CallbackWrapper;
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

