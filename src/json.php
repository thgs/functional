<?php

namespace thgs\Functional;

use thgs\Functional\Data\Either;

/**
 * Will allow JsonExceptions to be thrown
 *
 * @param int<1,max> $depth
 * @return Either<array{int,string},string>
 */
function eitherJsonEncode(mixed $value, int $flags = 0, int $depth = 512): Either
{
    $result = \json_encode($value, $flags, $depth);
    if (!$result) {
        return left([\json_last_error(), \json_last_error_msg()]);
    }
    return right($result);
}

/**
 * Will allow JsonExceptions to be thrown
 *
 * @param int<1,max> $depth
 * @return Either<array{int,string},mixed>
 */
function eitherJsonDecode(string $json, ?bool $associative = null, int $depth = 512, int $flags = 0): Either
{
    $result = \json_decode($json, $associative, $depth, $flags);
    if ($result === null) {
        return left([\json_last_error(), \json_last_error_msg()]);
    }
    return right($result);
}
