<?php

require \dirname(__DIR__, 2) . '/vendor/autoload.php';

use function thgs\Functional\appendFile;
use function thgs\Functional\dn;
use function thgs\Functional\putStrLn;
use function thgs\Functional\readFile;
use function thgs\Functional\show;
use function thgs\Functional\writeFile;

$ioAction = dn(
    // IO<void>
    writeFile("test.txt", show (123 * 123)),

    // IO<void>    therefore will use (>>)
    appendFile("test.txt", "Hello!\n"),

    // IO<string>  therefore will use (>>)
    readFile("test.txt"),

    // callable    therefore will use (>>=)
    putStrLn(...)
);

$ioAction();
