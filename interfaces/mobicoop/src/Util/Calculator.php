<?php

namespace App\Util;

class Calculator
{
    public function randAndSquare($a=0, $b=100)
    {
        $rdmNb = random_int($a, $b);
        return $rdmNb * $rdmNb;
    }
}
