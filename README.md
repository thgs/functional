## functional

This is a very experimental repository where I try to see how some functional
concepts can be expressed and constructed in PHP.

I tried initially to express some concepts as good as I could understand them
and very quickly realised that a very very similar approach was used in
[marcosh/lamphpda](https://github.com/marcosh/lamphpda). That is a more complete
library.

### Highlight features

##### Basic structures from Haskell

```php

$maybeInt = new Maybe(new Just(123));
$maybeInt = $maybeInt->fmap(fn ($x) => $x * 2);

if ($maybeInt->isJust()) {
    print $maybeInt->unwrap();
}

```

Including `Maybe`, `Either`, `IO` with instances as functors, applicative functors and monads.

##### Expression helpers

Composition of function calls

```php

$result = c (fn ($x) => $x + 2) (123); // 125

```

Function composition

```php

$f = fn ($x) => $x + 2;
$g = fn ($x) => $x * 2;
$gf = c($f)->fmap($g); // equivalent of g(f($x))

print $gf(123);  // (123 + 2) * 2 = 250

```

Wrap any php function

```
$min = c('min');

$maybeArrayOfInt = new Maybe(new Just(range(1,4)));

$maybeMinOfArray = fmap($min, $maybeArrayOfInt);

var_dump($maybeMinOfArray->unwrap());

```

Do notation

```php

// putStrLn :: String -> IO ()
$putStrLn = fn (string $x) => IO::inject(fn () => print $x . "\n");

// getLine :: IO String
$getLine = IO::inject(fn (): string => fgets(\STDIN));

$bound = dn($getLine, $putStrLn);
$bound(); // will run getLine and then bind the result to putStrLn and print it

```

##### Wrap existing code

```php

/**
 * Create a CallbackWrapper that connects the psr3Logger to a Tuple input.
 * However here we can also pass the `fmap` method.
 */
$wrapper = new CallbackWrapper(
    fn (Tuple $p) => $psr3Logger->log($p->fst(), $p->snd()),
    ['fmap' => fn (callable $f) => c ($f) ->fmap ($this->wiring)]
);

/**
 * We create the contextLogger
 */
$contextLogger = $wrapper ->fmap ( fn (Tuple $p): Tuple => t ($prefix . $p->fst(), [$extraContext] + $p->snd() ) );

($contextLogger) (t ("wrapped one", ["contextt"]));

```

Note that the above is very draft/experimental still.

### Contributing

Feel free to add any PR, comments, issues or discussions!

### Documentation

See [`Documentation.org`](https://github.com/thgs/functional/blob/master/Documentation.org) file (Emacs org-file).


### Other interesting functional programming in PHP libraries/projects

Here will maintain a list of other libraries. Feel free to let me know of one I have missed.

- [marcosh/lamphpda](https://github.com/marcosh/lamphpda)
- [crell/fp](https://github.com/crell/fp)
- [loophp/repository-monadic-helper](https://github.com/loophp/repository-monadic-helper)
- [haskellcamargo/php-maybe-monad](https://github.com/haskellcamargo/php-maybe-monad)
- [tmciver/functional-php](https://github.com/tmciver/functional-php)
- [phel-lang/phel-lang](https://github.com/phel-lang/phel-lang)
- [fp4php/functional](https://github.com/fp4php/functional)
- [krakphp/fn](https://github.com/krakphp/fn)
- [prolic/fpp](https://github.com/prolic/fpp)
- [phpfn/curry](https://github.com/phpfn/curry)
- [mathiasverraes/lambdalicious](https://github.com/mathiasverraes/lambdalicious)
- [haskellcamargo/yay-partial-functions](https://github.com/haskellcamargo/yay-partial-functions)
- [haskellcamargo/php-partial-function-application](https://github.com/haskellcamargo/php-partial-function-application)
- [loophp/combinator](https://github.com/loophp/combinator)
- [friends-of-reactphp/partial](https://github.com/friends-of-reactphp/partial)
- [fp4php/fp4php](https://github.com/fp4php/fp4php)
- [matteosister/php-curry](https://github.com/matteosister/php-curry)
- [munusphp/munus](https://github.com/munusphp/munus)
- [kapolos/pramda](https://github.com/kapolos/pramda)
- [ace411/bingo-functional](https://github.com/ace411/bingo-functional)
- [chippyash/Monad](https://github.com/chippyash/Monad)
- [jasny/improved-php-function](https://github.com/jasny/improved-php-function)
- [pitchart/transformer](https://github.com/pitchart/transformer)
- [functional-php/fantasy-land](https://github.com/functional-php/fantasy-land)
- [haskellcamargo/php-church-encoding](https://github.com/haskellcamargo/php-church-encoding)
- [functional-php/pattern-matching](https://github.com/functional-php/pattern-matching)
- [quack/quack](https://github.com/quack/quack)
- [functional-php/trampoline](https://github.com/functional-php/trampoline)
- [phunkie/phunkie](https://github.com/phunkie/phunkie)
- [baethon/phln](https://github.com/baethon/phln)
