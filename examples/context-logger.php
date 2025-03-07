<?php

require_once \dirname(__DIR__) . '/vendor/autoload.php';

use thgs\Functional\Data\Tuple;
use thgs\Functional\Typeclass\Adapter\CallableWiringFunctorAdapter;

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

function t(mixed $a, mixed $b) { return Tuple::new($a, $b); }
$prefix = 'functional: ';
$extraContext = 'contextual';

// Actual code -------------------------------

/**
 * We create a logger that is a functor and can fmap by passing a "wiring" fn,
 * conveniently this is a closure carrying the dependencies (Psr3Logger).
 */
$logger = new CallableWiringFunctorAdapter( fn (Tuple $p) => $psr3Logger->log($p->fst(), $p->snd()) );

/**
 * We start mapping functions over our functor.  The below will add prefix and
 * extraContext.
 */
$contextLogger = $logger ->fmap ( fn (Tuple $p): Tuple => t ($prefix . $p->fst(), [$extraContext] + $p->snd() ) );

/**
 * We call the logger, (only one method; log() as per wiring) and actually log things
 */
$contextLogger (t ("one",   ["context"]) );
$contextLogger (t ("two",   ["context"]) );
$contextLogger (t ("three", ["context"]) );
$contextLogger (t ("four",  ["context"]) );

return;

// Below fails
// todo: what if in the above line that now passes callable(Tuple):Tuple, we fmap with callable(Tuple):Int

$mapFunction = function (Tuple $p): Int {
    return strlen($p->fst());
};
$addedFunctionality = $contextLogger -> fmap ( $mapFunction );

$return = $addedFunctionality (t ("added", ["additional"]));
var_dump($return);

