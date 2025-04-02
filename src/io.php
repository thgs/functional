<?php

namespace thgs\Functional;

use thgs\Functional\Control\IO;
use thgs\Functional\Instance\Composition;

/**
 * @return IO<string|false>
 */
function getLine(): IO
{
    return IO::inject(fn (): string|false => fgets(\STDIN));
}

/**
 * @param string|\Stringable $message
 * @return IO<void>
 */
function putStrLn(\Stringable|string $message): IO
{
    return IO::inject(function () use ($message): void {
        print $message . PHP_EOL;
    });
}

/**
 * @param \Closure(string):string $f
 * @return IO<void>
 *
 * @todo can we not have Composition as $f ?
 */
function interact(\Closure $f): IO
{
    return getLine()
        ->bind(function (string|false $contents) use ($f): IO {
            // todo: fix the below, maybe this handling should not be here but in getLine.
            $contents = $contents === false ? 'no contents' : $contents;
            return putStrLn ($f ($contents));
        });
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
 * @return IO<int<0,max>|false>
 */
function writeFile(\Stringable|string $filename, \Stringable|string $contents): IO
{
    return IO::inject(fn (): int|false => file_put_contents($filename, $contents));
}

/**
 * @param \Stringable|string $filename
 * @param \Stringable|string $contents
 * @return IO<int<0,max>|false>
 */
function appendFile(\Stringable|string $filename, \Stringable|string $contents): IO
{
    return IO::inject(fn (): int|false => file_put_contents($filename, $contents, \FILE_APPEND));
}
