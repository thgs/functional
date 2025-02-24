<?php

namespace thgs\Functional\Typeclass;

interface ApplicativeInstance
{
    /**
     * pure :: a -> f a  
     */
    public static function pure(mixed $a): ApplicativeInstance;

    /**
     * (<*>) :: f (a -> b) -> f a -> f b 
     */
    public function sequence(ApplicativeInstance $functorWithFunction): ApplicativeInstance;
}
