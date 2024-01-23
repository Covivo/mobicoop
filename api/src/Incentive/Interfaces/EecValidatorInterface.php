<?php

namespace App\Incentive\Interfaces;

interface EecValidatorInterface
{
    public function isEecCompliant(): bool;
}
