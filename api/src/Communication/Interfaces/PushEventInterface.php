<?php

namespace App\Communication\Interfaces;

interface PushEventInterface
{
    public function execute(): bool;
}
