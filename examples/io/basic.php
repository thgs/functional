<?php

require \dirname(__DIR__, 2) . '/vendor/autoload.php';

use function thgs\Functional\interact;
use function thgs\Functional\putStrLn;

interact('strtoupper')->getValue();
