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


/**
 * @param \Stringable|string $filename
 * @return IO<string|false>
 */
function readFile(\Stringable|string $filename): IO
{
    return IO::inject(fn () => file_get_contents($filename));
}

/**
 * @param \Stringable|string $filename
 * @param \Stringable|string $contents
 * @return IO<int|false>
 */
function writeFile(\Stringable|string $filename, \Stringable|string $contents): IO
{
    return IO::inject(fn () => file_put_contents($filename, $contents));
}

/**
 * @param \Stringable|string $filename
 * @param \Stringable|string $contents
 * @return IO<int|false>
 */
function appendFile(\Stringable|string $filename, \Stringable|string $contents): IO
{
    return IO::inject(fn () => file_put_contents($filename, $contents, \FILE_APPEND));
}
