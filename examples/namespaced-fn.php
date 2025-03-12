<?php

namespace a\b\c {
    function myFunc($x) {
        return $x + 123;
    }
}

namespace {
    use function a\b\c\myFunc;

    $result = myFunc(123);
    print $result . "\n"; // works

    $result = 123 |> myFunc(...); // exception, see below
    print $result . "\n"; // works

    /**
Fatal error: Uncaught Error: Call to undefined function myFunc() in /home/thgs/Projects/Functional-PHP/functional/examples/namespaced-fn.php:15
Stack trace:
#0 {main}
  thrown in /home/thgs/Projects/Functional-PHP/functional/examples/namespaced-fn.php on line 15

  I am at d5a5dc8d (func-composition)
    */
}
