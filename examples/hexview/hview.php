<?php

use thgs\Functional\Data\Tuple;
use thgs\Functional\Typeclass\BifunctorInstance;
use function thgs\Functional\dn;
use function thgs\Functional\t;
use function thgs\Functional\readFile;

require \dirname(__DIR__, 2) . '/vendor/autoload.php';

$bytesPerLine = $argv[2] ?? 8;

$ioString = readFile($argv[1]);
$contents = (string) $ioString->getValue();

$tuple = t($contents, $contents);
$hexView = $tuple->bimap(
    fn ($x) => bin2hex($x),
    fn ($x) => implode('', array_map(fn ($c) => ord($c) <= 32 ? "." : $c, str_split($x, 1)))
);

$formattedHexView = $hexView->bimap(
    fn ($hex) => explode("\n", wordwrap($hex, $bytesPerLine * 2, "\n", true)),
    fn ($ascii) => explode("\n", wordwrap($ascii, $bytesPerLine, "\n", true))
);

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

$lines = zip($formattedHexView->fst(), $formattedHexView->snd());

foreach ($lines as $line)
    print sprintf(
        "%s %s",
        str_pad(
            implode(' ', str_split($line->fst(), 2)),
            ($bytesPerLine * 2) + ($bytesPerLine - 1)
        ),
        $line->snd()
    ) . PHP_EOL;
