<?php

require \dirname(__DIR__, 2) . '/vendor/autoload.php';

use function thgs\Functional\appendFile;
use function thgs\Functional\dn;
use function thgs\Functional\putStrLn;
use function thgs\Functional\readFile;
use function thgs\Functional\show;
use function thgs\Functional\writeFile;

dn(
    writeFile("test.txt", show (123 * 123)),
    appendFile("test.txt", "Hello!\n"),
    readFile("test.txt"),
    putStrLn(...)
)();
