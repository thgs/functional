<?php

use thgs\Functional\Data\Tuple;
use thgs\Functional\Control\IO;
use thgs\Functional\Typeclass\BifunctorInstance;
use function thgs\Functional\c;
use function thgs\Functional\dn;
use function thgs\Functional\t;
use function thgs\Functional\readFile;

require \dirname(__DIR__, 2) . '/vendor/autoload.php';

// Ignore the toying with the `define` here
define('BYTES_PER_LINE',          $argv[2] ?? 8);
define('DOUBLED_BYTES_PER_LINE',  BYTES_PER_LINE * 2);
define('HEX_PART_LENGTH',         DOUBLED_BYTES_PER_LINE + (BYTES_PER_LINE - 1));
define('LINE_FORMAT',             "%s %s" . PHP_EOL); // todo: set it from arg[3] but needs better arg parsing

// functions
$hexView = fn (Tuple $p) => $p->bimap(
    bin2hex(...),
    fn ($x) => implode('', array_map(fn ($c) => ord($c) <= 32 ? "." : $c, str_split($x, 1)))
);

$formattedHexView = fn (Tuple $p) => $p->bimap(
    fn ($hex) => explode(PHP_EOL, wordwrap($hex, DOUBLED_BYTES_PER_LINE, PHP_EOL, true)),
    fn ($ascii) => explode(PHP_EOL, wordwrap($ascii, BYTES_PER_LINE, PHP_EOL, true))
);

// rendering
/**
 * @template A
 * @template B
 * @param array<A> $a
 * @param array<B> $b
 * @return array<Tuple<A,B>>
 */
function zip(array $a, array $b): array
{
    // iterative implementation
    $result = [];
    foreach ($a as $k => $elemA) {
        $result[] = t($elemA, $b[$k]);
    } 
    return $result;
}

$asciiRenderer = function (Tuple $p) use ($bytesPerLine): IO {
    $lines = zip($p->fst(), $p->snd());
    foreach ($lines as $line)
        print sprintf(
            LINE_FORMAT,
            str_pad(
                implode(' ', str_split($line->fst(), 2)),
                HEX_PART_LENGTH,
            ),
            $line->snd()
        );
    // must return the unit
    return IO::unit();
};

$main = dn(
    readFile($argv[1]),

    fn ($x) => IO::inject(...)
        (c (Tuple::dupe(...)) ->fmap ($hexView) ->fmap ($formattedHexView)
            ($x)),

    /** or otherwise,
    fn (Tuple $p) => IO::inject($hexView($p)),
    fn (Tuple $p) => IO::inject($formattedHexView($p)),
    */

    $asciiRenderer
) ();

