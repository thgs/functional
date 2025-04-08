## functional

This is a very experimental repository where I try to see how some functional
concepts can be expressed and constructed in PHP.

I tried initially to express some concepts as good as I could understand them
and very quickly realised that a very very similar approach was used in
[marcosh/lamphpda](https://github.com/marcosh/lamphpda). That is a more complete
library.

From then on, I have deviated considerably and certain decisions are
quite different, for reasons that either I still explore and I am not
certain about or for different goals and priorities.

### Highlight features

#### Basic structures from Haskell

```php

$maybeInt = new Maybe(new Just(123));
$maybeInt = $maybeInt->fmap(fn ($x) => $x * 2);

if ($maybeInt->isJust()) {
    print $maybeInt->unwrap();
}

```

Including `Maybe`, `Either`, `IO` with instances as functors, applicative functors and monads.

#### Expression helpers

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

```php
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

Or a more elaborate example

```php

dn(
    writeFile("test.txt", show (123 * 123)),
    appendFile("test.txt", "Hello!\n"),
    readFile("test.txt"),
    putStrLn(...)
)();

```

Generalised Notations

By defining the composition between the "elements"

```php

function myNotation(mixed ...$elements) {
    return (new LeftToRightNotation(new CategoryOfFunctions()))->composeMany(...$elements);
}

// this will compose a function that first will evaluate the last passed "element"
$composedFunction = myNotation(
     fn (int $x): bool => $x == 16,
     fn (int $x): int  => (int) ($x / 2),
     fn (int $x): int => pow($x, 5),
     fn (array $items): int => count($items),
     array_filter(...)
);

$composedFunction(["one", "two", ""]); // true
```

#### Helpers for your tests

```php

class MyType implements FunctorInstance
{
    public function fmap(\Closure $f): FunctorInstance
    {
        // your code here
    }
}

class MyTypeTest
{
    // use helper traits to prove your implementation of fmap abides by the Functor Law.
    use FunctorLawsAssertions;

    public function testIsAFunctor(): void
    {
        $myType = new MyType();

        // Provide an instance and two functions for this assertion

        $this->assertInstanceIsFunctor(
            $myType,
            fn (int $x): int => $x + 2,
            fn (int $x): int => $x + 2
        );
    }
}

```

#### Wrap existing code

```php

/**
 * Create a Wrapper with an anonymous function that calls the psr3Logger from a Tuple input.
 */
$wrapper = Wrapper::withAdjustedInput(
    fn (Tuple $p) => $psr3Logger -> log ($p->fst(), $p->snd()),
);

/**
 * Let's add a prefix
 */
$prefix = 'prefixHere: ';

/**
 * Adjust the input
 */
$contextLogger = $wrapper -> adjustInput (fn (Tuple $p): Tuple => t ($prefix . $p->fst(), [$p->snd()]) );

/**
 * Adjust the output
 */
$logger = $contextLogger -> adjustOutput (fn () => time());

$currentTime = $logger (t ("Log message", "context"));

```

#### Loads of bugs and inconsistencies

phpstan is complaining, the tests are not yet fully there but I only
have covered some portion of the functionality. Nevertheless, I expect
there are things that might have been implemented or type hinted wrong
at this point. Especially if you stress the limits of the definitions
or functionality.


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
