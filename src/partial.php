<?php

namespace thgs\Functional;

use thgs\Functional\Instance\Composition;
use function thgs\Functional\c;

/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Haskell Camargo <marcelocamargo@linuxmail.org>
 * Copyright (c) 2025 Theo Gotsopoulos <theogpl57@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @see https://github.com/haskellcamargo/php-partial-function-application/blob/master/src/partial.php
 *
 * File contains minor edits from the initial implementation. Likely to change
 * more as time goes by.
 *
 * @todo Type hinting this will be challenging.
 *
 * @return mixed|callable|Composition
 */
function partial(callable|Composition $f)
{
    $isComposition = $f instanceof Composition;
    
    $reflectionFunction = $isComposition
        ? $f->getReflectionFunction()
        : new \ReflectionFunction($f instanceof \Closure ? $f : \Closure::fromCallable($f));

    // Fetch the initial parameters on initialization
    $startParameters = array_slice(func_get_args(), 1);
    $requiredSize = $reflectionFunction->getNumberOfRequiredParameters();

    // When we have enough arguments to evaluate the function, the edge-case.
    if (sizeof($startParameters) >= $requiredSize) {
        return call_user_func_array(
            $isComposition ? unwrapC($f) : $f, $startParameters
        );
    }

    $partialFunction = function() use ($startParameters, $requiredSize, $f) {
        $restParameters = func_get_args();
        $remainingSize = $requiredSize - (count($restParameters) + count($startParameters));

        // Join the current parameters with the newly received parameters
        $allParams = array_merge($startParameters, $restParameters);

        // Append the function as the first item and call partialization again
        array_unshift($allParams, $f);
        return partial(...$allParams);
    };

    return $isComposition ? c ($partialFunction) : $partialFunction;
}
