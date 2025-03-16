<?php

namespace thgs\Functional;

use thgs\Functional\Data\IO;
use thgs\Functional\Instance\Composition;

/**
 * @return IO<string>
 */
function getLine(): IO
{
    return IO::inject(fn (): string => fgets(\STDIN));
}

/**
 * @param string|\Stringable $message
 * @return IO<void>
 */
function putStrLn(\Stringable|string $message): IO
{
    return IO::inject(fn () => print $message . PHP_EOL);
}

/**
 * @param Composition|callable $f
 * @return IO<void>
 */
function interact(Composition|callable $f): IO
{
    return getLine()->bind(fn ($contents) => putStrLn ($f ($contents)));
}
